<?php

namespace App\Controller;

use App\Repository\PropertyRepository;
use App\Repository\TenantRepository;
use App\Repository\RentReceiptRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/espace-bailleur', name: 'owner_dashboard')]
class OwnerDashboardController extends AbstractController
{
    public function __invoke(PropertyRepository $propertyRepository, TenantRepository $tenantRepository, RentReceiptRepository $rentReceiptRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_TEAM');

        $stats = [
            'properties' => $propertyRepository->count([]),
            'tenants' => $tenantRepository->count([]),
            'receipts' => $rentReceiptRepository->count([]),
        ];

        return $this->render('dashboard/owner.html.twig', [
            'stats' => $stats,
        ]);
    }
}
