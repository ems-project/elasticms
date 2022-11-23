<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Generator\Pdf;

use EMS\CommonBundle\Service\Pdf\PdfPrintOptions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface PdfGeneratorInterface
{
    public function createOptionsFromHtml(string $html): PdfPrintOptions;

    public function generateResponseFromHtml(string $html, ?PdfPrintOptions $options = null): Response;

    public function generateStreamedResponseFromHtml(string $html, ?PdfPrintOptions $options = null): StreamedResponse;
}
