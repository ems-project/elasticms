<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Service\Pdf;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DomPdfPrinter implements PdfPrinterInterface
{
    /** @var string[] */
    private array $domPdfRootDirectories;

    public function __construct(string $projectDir, string $cacheDir)
    {
        $this->domPdfRootDirectories = [$projectDir];
        if (!\str_starts_with($cacheDir, $projectDir)) {
            $this->domPdfRootDirectories[] = $cacheDir;
        }
        $sysCacheDir = \sys_get_temp_dir();
        if (!\str_starts_with($sysCacheDir, $projectDir) && !\str_starts_with($sysCacheDir, $cacheDir)) {
            $this->domPdfRootDirectories[] = $cacheDir;
        }
    }

    public function getPdfOutput(PdfInterface $pdf, ?PdfPrintOptions $options = null): PdfOutput
    {
        $options ??= new PdfPrintOptions([]);
        $dompdf = $this->makeDomPdf($pdf, $options);

        return new PdfOutput(fn (): ?string => $dompdf->output());
    }

    public function getStreamedResponse(PdfInterface $pdf, ?PdfPrintOptions $options = null): StreamedResponse
    {
        $options ??= new PdfPrintOptions([]);
        $dompdf = $this->makeDomPdf($pdf, $options);

        return new StreamedResponse(function () use ($dompdf, $pdf, $options) {
            $dompdf->stream($pdf->getFileName(), [
                'compress' => (int) $options->isCompress(),
                'Attachment' => (int) $options->isAttachment(),
            ]);
        });
    }

    private function makeDomPdf(PdfInterface $pdf, PdfPrintOptions $options): Dompdf
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($pdf->getHtml());
        $dompdf->setPaper($options->getSize(), $options->getOrientation());
        $dompdf->setOptions(new Options([
            'isHtml5ParserEnabled' => $options->isHtml5Parsing(),
            'isPhpEnabled' => $options->isPhpEnabled(),
            'chroot' => \array_filter(\array_merge($this->domPdfRootDirectories, [$options->getChroot()])),
        ]));
        $dompdf->render();

        return $dompdf;
    }
}
