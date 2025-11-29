<?php

declare(strict_types=1);

namespace EMS\Helpers\Pdf;

use EMS\Helpers\File\TempFile;
use EMS\Helpers\Standard\Type;
use setasign\Fpdi\Tcpdf\Fpdi;

class PdfSplitter
{
    private readonly Fpdi $pdf;
    private readonly int $pageCount;

    public function __construct(private readonly string $filename)
    {
        $this->pdf = new Fpdi();
        $this->pageCount = $this->pdf->setSourceFile($filename);
    }

    public function extractFirstPages(int $maxPages = 50): string
    {
        return $this->split(1, $maxPages);
    }

    public function split(int $from, ?int $length = null): string
    {
        if ($from < 1) {
            throw new \RuntimeException('From must be greater than 0');
        }
        if (($length ?? 1) < 1) {
            throw new \RuntimeException('Length must be greater than 0');
        }
        if ($from > $this->pageCount) {
            throw new \RuntimeException('From must be smaller than the last page');
        }
        $length ??= $this->pageCount;
        $maxPages = \min($from + $length - 1, $this->pageCount);
        if (1 === $from && $maxPages >= $this->pageCount) {
            return $this->filename;
        }

        for ($i = $from; $i <= $maxPages; ++$i) {
            $pageId = $this->pdf->importPage($i);
            $size = Type::array($this->pdf->getTemplateSize($pageId));
            $this->pdf->addPage(Type::string($size['orientation']), [Type::float($size['width']), Type::float($size['height'])]);
            $this->pdf->useTemplate($pageId);
        }

        $tempFile = TempFile::create();
        $this->pdf->Output($tempFile->path, 'F');

        return $tempFile->path;
    }
}
