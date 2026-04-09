<?php

namespace App\Controller;

use App\Entity\RentReceipt;
use App\Entity\Tenant;
use App\Repository\RentReceiptRepository;
use App\Service\RentReceiptPdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/espace-locataire/quittances', name: 'tenant_quittance_')]
class TenantRentReceiptController extends AbstractController
{
    public function __construct(
        private RentReceiptPdfService $pdfService,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(RentReceiptRepository $repo): Response
    {
        $tenant = $this->getUser();
        if (!$tenant instanceof Tenant) {
            return $this->redirectToRoute('homepage');
        }

        $receipts = $repo->findBy(
            ['tenant' => $tenant],
            ['issuedAt' => 'DESC']
        );

        return $this->render('tenant/quittances_index.html.twig', [
            'tenant'   => $tenant,
            'receipts' => $receipts,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(RentReceipt $receipt): Response
    {
        $tenant = $this->getUser();
        if (!$tenant instanceof Tenant) {
            return $this->redirectToRoute('homepage');
        }

        // Sécurité : la quittance doit appartenir au locataire connecté
        if ($receipt->getTenant()?->getId() !== $tenant->getId()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('tenant/quittances_show.html.twig', [
            'tenant'  => $tenant,
            'receipt' => $receipt,
        ]);
    }

    #[Route('/{id}/pdf', name: 'pdf', methods: ['GET'])]
    public function pdf(RentReceipt $receipt): Response
    {
        $tenant = $this->getUser();
        if (!$tenant instanceof Tenant) {
            return $this->redirectToRoute('homepage');
        }

        if ($receipt->getTenant()?->getId() !== $tenant->getId()) {
            throw $this->createAccessDeniedException();
        }

        $content = $this->pdfService->generate($receipt);

        return new Response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="quittance-'.$receipt->getNumber().'.pdf"',
        ]);
    }
}
