<?php

namespace App\Controller;

use App\Entity\Tenant;
use App\Form\TenantSetPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class TenantSetPasswordController extends AbstractController
{
    #[Route('/definir-mot-de-passe/{token}', name: 'app_tenant_set_password')]
    public function setPassword(
        string $token,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        /** @var Tenant|null $tenant */
        $tenant = $entityManager->getRepository(Tenant::class)->findOneBy(['invitationToken' => $token]);

        if (!$tenant || !$tenant->isInvitationTokenValid()) {
            $this->addFlash('error', 'Ce lien est invalide ou a expiré. Contactez votre bailleur.');
            return $this->redirectToRoute('homepage');
        }

        $form = $this->createForm(TenantSetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            $tenant->setPassword($passwordHasher->hashPassword($tenant, $plainPassword));
            $tenant->setInvitationToken(null);
            $tenant->setInvitationTokenExpiresAt(null);

            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été défini. Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('homepage');
        }

        return $this->render('registration/tenant_set_password.html.twig', [
            'form' => $form,
            'tenant' => $tenant,
        ]);
    }
}
