<?php

declare(strict_types=1);

namespace EMS\Xliff\Writer;

use EMS\Helpers\Standard\Type;
use EMS\Xliff\Model\Document;
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
        $dom = DomHelper::initDocument($this->options->preserveWhitespace, $this->options->formatOutput);
        $xliff = DomHelper::initXliff($dom, Version::V12, Version::V12_NAMESPACE);
        foreach ($package->getDocuments() as $document) {
            $this->addDocument($xliff, $package, $document);
        }

        return Type::string($dom->saveXML());
    }

    private function addDocument(\DOMElement $xliff, Package $package, Document $document): void
    {
        $file = new \DOMElement('file');
        $file->setAttribute('source-language', $package->getSourceLocale());
        $file->setAttribute('target-language', $package->getTargetLocale());
        $file->setAttribute('original', $document->id);
        $file->setAttribute('datatype', 'database');
        $xliff->appendChild($file);
    }
}
