<?php

namespace App\Controller;

use App\Entity\Tenant;
use App\Form\TenantRegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class TenantRegistrationController extends AbstractController
{
    #[Route('/inscription-locataire', name: 'app_register_tenant')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('tenant_dashboard');
        }

        $tenant = new Tenant();
        $form = $this->createForm(TenantRegistrationFormType::class, $tenant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            $tenant->setPassword($passwordHasher->hashPassword($tenant, $plainPassword));

            $entityManager->persist($tenant);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte locataire a été créé. Vous pouvez maintenant vous connecter.');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('registration/register_tenant.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
