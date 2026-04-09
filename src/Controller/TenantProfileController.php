<?php

namespace App\Controller;

use App\Entity\Tenant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/espace-locataire/profil', name: 'tenant_profile_')]
class TenantProfileController extends AbstractController
{
    #[Route('', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Tenant) {
            return $this->redirectToRoute('homepage');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $name     = trim($request->request->get('name', ''));
            $email    = trim($request->request->get('email', ''));
            $phone    = trim($request->request->get('phone', ''));
            $newPass  = $request->request->get('newPassword', '');
            $confPass = $request->request->get('confirmPassword', '');

            if ($name === '') {
                $error = 'Le nom est obligatoire.';
            } elseif ($email === '') {
                $error = 'L\'adresse email est obligatoire.';
            } elseif ($newPass !== '' && $newPass !== $confPass) {
                $error = 'Les mots de passe ne correspondent pas.';
            } elseif ($newPass !== '' && strlen($newPass) < 8) {
                $error = 'Le mot de passe doit contenir au moins 8 caractères.';
            } else {
                $user->setName($name);
                $user->setEmail($email);
                $user->setPhone($phone ?: null);

                if ($newPass !== '') {
                    $user->setPassword($hasher->hashPassword($user, $newPass));
                }

                $em->flush();
                $this->addFlash('success', 'Profil mis à jour avec succès.');

                return $this->redirectToRoute('tenant_profile_edit');
            }
        }

        return $this->render('profile/tenant_edit.html.twig', [
            'user'  => $user,
            'error' => $error,
        ]);
    }
}
