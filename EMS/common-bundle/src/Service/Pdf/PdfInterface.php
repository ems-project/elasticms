<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Service\Pdf;

interface PdfInterface
{
    public const FILENAME = 'filename';

    public function getFilename(): string;

    public function getHtml(): string;
}
