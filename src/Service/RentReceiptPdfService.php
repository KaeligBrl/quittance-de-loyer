<?php

namespace App\Service;

use App\Entity\RentReceipt;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class RentReceiptPdfService
{
    public function __construct(
        private Environment $twig,
    ) {}

    public function generate(RentReceipt $receipt, ?object $owner = null): string
    {
        $html = $this->twig->render('rent_receipt/pdf.html.twig', [
            'receipt' => $receipt,
            'owner'   => $owner,
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
