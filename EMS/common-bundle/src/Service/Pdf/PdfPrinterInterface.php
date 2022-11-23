<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Service\Pdf;

use Symfony\Component\HttpFoundation\StreamedResponse;

interface PdfPrinterInterface
{
    public function getPdfOutput(PdfInterface $pdf, ?PdfPrintOptions $options = null): PdfOutput;

    public function getStreamedResponse(PdfInterface $pdf, ?PdfPrintOptions $options = null): StreamedResponse;
}
