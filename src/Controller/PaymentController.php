<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\RentReceipt;
use App\Form\PaymentType;
use App\Service\RentReceiptMailerService;
use App\Service\RentReceiptPdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/paiements')]
final class PaymentController extends AbstractController
{
    public function __construct(
        private RentReceiptPdfService $pdfService,
        private RentReceiptMailerService $mailerService,
    ) {}

    #[Route(name: 'app_payment_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $payments = $entityManager
            ->getRepository(Payment::class)
            ->findAll();

        return $this->render('payment/index.html.twig', [
            'payments' => $payments,
        ]);
    }

    #[Route('/marquer-paye/{id}', name: 'app_payment_quick', methods: ['POST'])]
    public function marquerPaye(RentReceipt $rentReceipt, EntityManagerInterface $em, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('quick_pay_'.$rentReceipt->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $payment = new Payment();
        $payment->setRentReceipt($rentReceipt);
        $payment->setAmount($rentReceipt->getAmount());
        $payment->setPaidAt(new \DateTime());
        $payment->setMethod('virement');

        $em->persist($payment);
        $em->flush();

        $this->sendReceiptPdfByEmail($rentReceipt);

        $this->addFlash('success', 'Paiement de '.$rentReceipt->getAmount().' € enregistré (virement) pour la quittance '.$rentReceipt->getNumber().'. La quittance PDF a été envoyée par e-mail au locataire.');

        return $this->redirectToRoute('app_rent_receipt_index');
    }

    #[Route('/new', name: 'app_payment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $payment = new Payment();

        // Pré-remplissage depuis une quittance passée en paramètre
        if ($rentReceiptId = $request->query->get('quittance')) {
            $rentReceipt = $entityManager->getRepository(RentReceipt::class)->find($rentReceiptId);
            if ($rentReceipt) {
                $payment->setRentReceipt($rentReceipt);
                $payment->setAmount($rentReceipt->getAmount());
            }
        }

        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($payment);
            $entityManager->flush();

            $rentReceipt = $payment->getRentReceipt();
            if ($rentReceipt) {
                $this->sendReceiptPdfByEmail($rentReceipt);
                $this->addFlash('success', 'Paiement enregistré. La quittance PDF a été envoyée par e-mail au locataire.');
            }

            return $this->redirectToRoute('app_payment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('payment/new.html.twig', [
            'payment' => $payment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_payment_show', methods: ['GET'])]
    public function show(Payment $payment): Response
    {
        return $this->render('payment/show.html.twig', [
            'payment' => $payment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_payment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Payment $payment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_payment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('payment/edit.html.twig', [
            'payment' => $payment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_payment_delete', methods: ['POST'])]
    public function delete(Request $request, Payment $payment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$payment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($payment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_payment_index', [], Response::HTTP_SEE_OTHER);
    }

    private function sendReceiptPdfByEmail(RentReceipt $rentReceipt): void
    {
        try {
            $owner = $this->getUser();
            $pdf = $this->pdfService->generate($rentReceipt, $owner);
            $this->mailerService->sendToTenant($rentReceipt, $pdf);
        } catch (\Throwable $e) {
            // Ne pas bloquer le flux si l'envoi échoue
            $this->addFlash('warning', 'Paiement enregistré, mais l\'envoi de l\'e-mail a échoué : '.$e->getMessage());
        }
    }
}
