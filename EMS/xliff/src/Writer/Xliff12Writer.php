<?php

declare(strict_types=1);

namespace EMS\Xliff\Writer;

use EMS\Helpers\Standard\Type;
use EMS\Xliff\Model\Package;
use EMS\Xliff\Options;
use EMS\Xliff\Version;
use EMS\Xliff\XML\DomHelper;

class Xliff12Writer implements WriterInterface
{
    public function __construct(private readonly Options $options)
    {
    }

    public function supportsVersion(string $version): bool
    {
        return Version::V12 === $version;
    }

    public function write(Package $package): string
    {
        $dom = DomHelper::initDocument(Version::V12);

        return Type::string($dom->saveXML());
    }
}
