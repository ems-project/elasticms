<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Service\Pdf;

use EMS\CommonBundle\Contracts\Generator\Pdf\PdfGeneratorInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfGenerator implements PdfGeneratorInterface
{
    public function __construct(private readonly PdfPrinterInterface $pdfPrinter)
    {
    }

    #[\Override]
    public function createOptionsFromHtml(string $html): PdfPrintOptions
    {
        return new PdfPrintOptionsHtml($html);
    }

    #[\Override]
    public function generateResponseFromHtml(string $html, ?PdfPrintOptions $options = null): Response
    {
        $options ??= new PdfPrintOptions([]);
        $pdf = new Pdf($options->getFilename(), $html);

        $pdfOutput = $this->pdfPrinter->getPdfOutput($pdf, $options);

        $response = new Response();
        $response->setContent($pdfOutput->make());

        $dispositionType = $options->isAttachment() ? HeaderUtils::DISPOSITION_ATTACHMENT : HeaderUtils::DISPOSITION_INLINE;
        $disposition = HeaderUtils::makeDisposition($dispositionType, $pdf->getFilename());
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }

    #[\Override]
    public function generateStreamedResponseFromHtml(string $html, ?PdfPrintOptions $options = null): StreamedResponse
    {
        $options ??= new PdfPrintOptions([]);
        $pdf = new Pdf($options->getFilename(), $html);

        return $this->pdfPrinter->getStreamedResponse($pdf, $options);
    }
}
