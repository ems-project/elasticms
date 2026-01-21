<?php

declare(strict_types=1);

namespace EMS\Xliff\Reader;

use EMS\Xliff\Model\Package;
use EMS\Xliff\Options;
use EMS\Xliff\XML\DomHelper;

class Xliff22Reader implements ReaderInterface
{
    public function __construct(private readonly Options $options)
    {
    }

    public function supports(string $xml): bool
    {
        return \str_contains($xml, 'version="1.2"') || \str_contains($xml, 'urn:oasis:names:tc:xliff:document:1.2');
    }

    public function read(string $xml): Package
    {
        $dom = DomHelper::loadXml($xml);

        return new Package();
    }
}
