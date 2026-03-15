<?php

namespace App\Controller;

use App\Entity\Tenant;
use App\Repository\RentReceiptRepository;
use App\Repository\TenantFileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/espace-locataire', name: 'tenant_dashboard')]
class TenantDashboardController extends AbstractController
{
    public function __invoke(RentReceiptRepository $rentReceiptRepository, TenantFileRepository $fileRepository): Response
    {
        $tenant = $this->getUser();
        if (!$tenant instanceof Tenant) {
            return $this->redirectToRoute('homepage');
        }

        $receipts = $rentReceiptRepository->findBy(['tenant' => $tenant], ['issuedAt' => 'DESC']);

        $filesByCategory = [];
        foreach (\App\Entity\TenantFile::CATEGORIES as $key => $label) {
            $filesByCategory[$key] = [
                'label'    => $label,
                'required' => in_array($key, \App\Entity\TenantFile::REQUIRED_CATEGORIES),
                'files'    => [],
            ];
        }
        foreach ($tenant->getFiles() as $f) {
            $filesByCategory[$f->getCategory()]['files'][] = $f;
        }

        return $this->render('dashboard/tenant.html.twig', [
            'tenant'          => $tenant,
            'receipts'        => $receipts,
            'filesByCategory' => $filesByCategory,
        ]);
    }
}
