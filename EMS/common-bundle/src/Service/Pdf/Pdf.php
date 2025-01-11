<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Service\Pdf;

final readonly class Pdf implements PdfInterface
{
    public function __construct(private string $filename, private string $html)
    {
    }

    #[\Override]
    public function getFilename(): string
    {
        return $this->filename;
    }

    #[\Override]
    public function getHtml(): string
    {
        return $this->html;
    }
}
