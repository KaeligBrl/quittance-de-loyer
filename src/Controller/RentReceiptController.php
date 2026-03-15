<?php

namespace App\Controller;

use App\Entity\RentReceipt;
use App\Entity\Tenant;
use App\Form\RentReceiptType;
use App\Repository\RentReceiptRepository;
use App\Service\RentReceiptPdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quittances')]
final class RentReceiptController extends AbstractController
{
    public function __construct(
        private RentReceiptPdfService $pdfService,
    ) {}

    #[Route(name: 'app_rent_receipt_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $rentReceipts = $entityManager
            ->getRepository(RentReceipt::class)
            ->findAll();

        return $this->render('rent_receipt/index.html.twig', [
            'rent_receipts' => $rentReceipts,
        ]);
    }

    #[Route('/new', name: 'app_rent_receipt_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, RentReceiptRepository $repo): Response
    {
        $rentReceipt = new RentReceipt();

        // Pré-remplir la date d'émission et la période courante
        $now = new \DateTime();
        $rentReceipt->setIssuedAt($now);
        $rentReceipt->setPeriodStart(new \DateTime('first day of this month'));
        $rentReceipt->setPeriodEnd(new \DateTime('last day of this month'));

        $form = $this->createForm(RentReceiptType::class, $rentReceipt);
        $form->handleRequest($request);

        // Carte {tenantId → infos montant} pour calcul dynamique côté JS
        $tenants = $entityManager->getRepository(Tenant::class)->findAll();
        $tenantAmounts = [];
        foreach ($tenants as $t) {
            $prop = $t->getProperty();
            $tenantAmounts[$t->getId()] = [
                'rent'     => $prop ? $prop->getRentAmount() : null,
                'charges'  => $prop ? $prop->getChargesAmount() : null,
                'total'    => $prop ? $prop->getTotalRent() : null,
                'property' => $prop ? $prop->getTitle() : null,
            ];
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $tenant = $rentReceipt->getTenant();

            // Calcul automatique du montant depuis la propriété du locataire
            $property = $tenant?->getProperty();
            if ($property && $property->getTotalRent() !== null) {
                $rentReceipt->setAmount((string) $property->getTotalRent());
            } elseif ($property && $property->getRentAmount() !== null) {
                $rentReceipt->setAmount($property->getRentAmount());
            } else {
                $this->addFlash('error', 'Impossible de calculer le montant : le bien du locataire n\'a pas de loyer défini.');
                return $this->render('rent_receipt/new.html.twig', ['rent_receipt' => $rentReceipt, 'form' => $form, 'tenant_amounts' => $tenantAmounts]);
            }

            // Auto-génération du numéro
            $number = $repo->generateNumber($tenant->getId(), $rentReceipt->getPeriodStart());
            $rentReceipt->setNumber($number);

            $entityManager->persist($rentReceipt);
            $entityManager->flush();

            $this->addFlash('success', 'Quittance ' . $number . ' créée — ' . $rentReceipt->getAmount() . ' €.');

            return $this->redirectToRoute('app_rent_receipt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rent_receipt/new.html.twig', [
            'rent_receipt'   => $rentReceipt,
            'form'           => $form,
            'tenant_amounts' => $tenantAmounts,
        ]);
    }

    #[Route('/{id}/pdf', name: 'app_rent_receipt_pdf', methods: ['GET'])]
    public function pdf(RentReceipt $rentReceipt): Response
    {
        $owner = $this->getUser();
        $content = $this->pdfService->generate($rentReceipt, $owner);

        return new Response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="quittance-'.$rentReceipt->getNumber().'.pdf"',
        ]);
    }

    #[Route('/{id}', name: 'app_rent_receipt_show', methods: ['GET'])]
    public function show(RentReceipt $rentReceipt): Response
    {
        return $this->render('rent_receipt/show.html.twig', [
            'rent_receipt' => $rentReceipt,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rent_receipt_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RentReceipt $rentReceipt, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RentReceiptType::class, $rentReceipt, ['include_amount' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_rent_receipt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rent_receipt/edit.html.twig', [
            'rent_receipt' => $rentReceipt,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rent_receipt_delete', methods: ['POST'])]
    public function delete(Request $request, RentReceipt $rentReceipt, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rentReceipt->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($rentReceipt);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_rent_receipt_index', [], Response::HTTP_SEE_OTHER);
    }
}
