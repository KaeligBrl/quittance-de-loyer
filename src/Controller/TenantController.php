<?php

namespace App\Controller;

use App\Entity\Tenant;
use App\Form\TenantType;
use App\Service\TenantInvitationMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/locataires')]
final class TenantController extends AbstractController
{
    #[Route(name: 'app_tenant_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $tenants = $entityManager
            ->getRepository(Tenant::class)
            ->findAll();

        return $this->render('tenant/index.html.twig', [
            'tenants' => $tenants,
        ]);
    }

    #[Route('/new', name: 'app_tenant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TenantInvitationMailerService $invitationMailer, LoggerInterface $logger): Response
    {
        $tenant = new Tenant();
        $form = $this->createForm(TenantType::class, $tenant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tenant->generateInvitationToken();
            $entityManager->persist($tenant);
            $entityManager->flush();

            $landlordName = $this->getUser()?->getName() ?? 'Votre bailleur';
            try {
                $invitationMailer->sendInvitation($tenant, $landlordName);
                $this->addFlash('success', sprintf('Le locataire %s a été créé et un email d\'invitation lui a été envoyé.', $tenant->getName()));
            } catch (\Exception $e) {
                $logger->error('Échec envoi invitation locataire : ' . $e->getMessage(), ['exception' => $e]);
                $this->addFlash('warning', sprintf('Le locataire a été créé mais l\'email n\'a pas pu être envoyé : %s', $e->getMessage()));
            }

            return $this->redirectToRoute('app_tenant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tenant/new.html.twig', [
            'tenant' => $tenant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tenant_show', methods: ['GET'])]
    public function show(Tenant $tenant): Response
    {
        return $this->render('tenant/show.html.twig', [
            'tenant' => $tenant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tenant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tenant $tenant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TenantType::class, $tenant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_tenant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tenant/edit.html.twig', [
            'tenant' => $tenant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/renvoyer-invitation', name: 'app_tenant_resend_invitation', methods: ['GET'])]
    public function resendInvitation(Tenant $tenant, EntityManagerInterface $entityManager, TenantInvitationMailerService $invitationMailer, LoggerInterface $logger): Response
    {
        $tenant->generateInvitationToken();
        $entityManager->flush();

        $landlordName = $this->getUser()?->getName() ?? 'Votre bailleur';
        try {
            $invitationMailer->sendInvitation($tenant, $landlordName);
            $this->addFlash('success', sprintf('L\'invitation a été renvoyée à %s.', $tenant->getEmail()));
        } catch (\Exception $e) {
            $logger->error('Échec renvoi invitation : ' . $e->getMessage(), ['exception' => $e]);
            $this->addFlash('warning', sprintf('Impossible d\'envoyer l\'email : %s', $e->getMessage()));
        }

        return $this->redirectToRoute('app_tenant_show', ['id' => $tenant->getId()]);
    }

    #[Route('/{id}', name: 'app_tenant_delete', methods: ['POST'])]
    public function delete(Request $request, Tenant $tenant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tenant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tenant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tenant_index', [], Response::HTTP_SEE_OTHER);
    }
}
