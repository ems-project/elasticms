<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Service\Pdf;

final class Pdf implements PdfInterface
{
    public function __construct(private readonly string $filename, private readonly string $html)
    {
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
