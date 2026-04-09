<?php

namespace App\Controller;

use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/espace-bailleur/profil', name: 'owner_profile_')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Team) {
            return $this->redirectToRoute('homepage');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $firstName = trim($request->request->get('firstName', ''));
            $lastName  = trim($request->request->get('lastName', ''));
            $email     = trim($request->request->get('email', ''));
            $newPass   = $request->request->get('newPassword', '');
            $confPass  = $request->request->get('confirmPassword', '');

            if ($email === '') {
                $error = 'L\'adresse email est obligatoire.';
            } elseif ($newPass !== '' && $newPass !== $confPass) {
                $error = 'Les mots de passe ne correspondent pas.';
            } elseif ($newPass !== '' && strlen($newPass) < 8) {
                $error = 'Le mot de passe doit contenir au moins 8 caractères.';
            } else {
                $user->setFirstName($firstName ?: null);
                $user->setLastName($lastName ?: null);
                $user->setEmail($email);

                if ($newPass !== '') {
                    $user->setPassword($hasher->hashPassword($user, $newPass));
                }

                $em->flush();
                $this->addFlash('success', 'Profil mis à jour avec succès.');

                return $this->redirectToRoute('owner_profile_edit');
            }
        }

        return $this->render('profile/edit.html.twig', [
            'user'  => $user,
            'error' => $error,
        ]);
    }
}
