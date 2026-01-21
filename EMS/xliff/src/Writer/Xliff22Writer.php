<?php

declare(strict_types=1);

namespace EMS\Xliff\Writer;

use EMS\Helpers\Standard\Type;
use EMS\Xliff\Model\Document;
use EMS\Xliff\Model\Package;
use EMS\Xliff\Options;
use EMS\Xliff\Version;
use EMS\Xliff\XML\DomHelper;

class Xliff22Writer implements WriterInterface
{
    public function __construct(private readonly Options $options)
    {
    }

    public function supportsVersion(string $version): bool
    {
        return Version::V22 === $version;
    }

    public function write(Package $package): string
    {
        $dom = DomHelper::initDocument($this->options->preserveWhitespace, $this->options->formatOutput);
        $xliff = DomHelper::initXliff($dom, Version::V22, Version::V22_NAMESPACE);
        $xliff->setAttribute('srcLang', $package->getSourceLocale());
        $xliff->setAttribute('trgLang', $package->getTargetLocale());

        foreach ($package->getDocuments() as $document) {
            $this->addDocument($xliff, $document);
        }

        return Type::string($dom->saveXML());
    }

    private function addDocument(\DOMElement $xliff, Document $document): void
    {
        $file = new \DOMElement('file');
        $file->setAttribute('id', $document->id);
        $file->setAttribute('original', $document->id);
        $xliff->appendChild($file);
    }
}
