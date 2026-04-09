<?php

namespace App\Service;

use App\Entity\RentReceipt;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;

class RentReceiptMailerService
{
    public function __construct(
        private MailerInterface $mailer,
        private string $senderEmail = 'contact@kaeligberel.fr',
        private string $senderName = 'Quittance de loyer',
    ) {}

    public function sendToTenant(RentReceipt $receipt, string $pdfContent): void
    {
        $tenant = $receipt->getTenant();
        if (!$tenant || !$tenant->getEmail()) {
            return;
        }

        $period = $receipt->getPeriodStart()->format('m/Y');
        $filename = 'quittance-'.$receipt->getNumber().'.pdf';

        $email = (new Email())
            ->from(\Symfony\Component\Mime\Address::create(sprintf('%s <%s>', $this->senderName, $this->senderEmail)))
            ->to($tenant->getEmail())
            ->subject(sprintf('Votre quittance de loyer — %s', $period))
            ->text(sprintf(
                "Bonjour %s,\n\nVeuillez trouver ci-joint votre quittance de loyer pour la période du %s au %s.\n\nMontant : %s €\n\nCordialement,\nVotre bailleur",
                $tenant->getName(),
                $receipt->getPeriodStart()->format('d/m/Y'),
                $receipt->getPeriodEnd()->format('d/m/Y'),
                number_format((float) $receipt->getAmount(), 2, ',', ' ')
            ))
            ->attach($pdfContent, $filename, 'application/pdf');

        $this->mailer->send($email);
    }
}
