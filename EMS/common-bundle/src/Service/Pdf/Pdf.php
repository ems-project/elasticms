<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Service\Pdf;

final class Pdf implements PdfInterface
{
    private string $filename;
    private string $html;

    public function __construct(string $filename, string $html)
    {
        $this->filename = $filename;
        $this->html = $html;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getHtml(): string
    {
        return $this->html;
    }
}
