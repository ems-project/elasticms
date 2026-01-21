<?php

declare(strict_types=1);

namespace EMS\Xliff;

use EMS\Xliff\Model\Document;
use EMS\Xliff\Model\Package;
use EMS\Xliff\Reader\ReaderRegistry;
use EMS\Xliff\Reader\Xliff12Reader;
use EMS\Xliff\Reader\Xliff22Reader;
use EMS\Xliff\Writer\WriterRegistry;
use EMS\Xliff\Writer\Xliff12Writer;
use EMS\Xliff\Writer\Xliff22Writer;

final class Xliff
{
    private Package $package;

    private function __construct(
        private readonly WriterRegistry $writers,
        private readonly ReaderRegistry $readers,
        private readonly Options $options,
    ) {
    }

    public static function createDefault(?Options $options = null): self
    {
        $options ??= new Options();

        $writers = new WriterRegistry([
            new Xliff12Writer($options),
            new Xliff22Writer($options),
        ]);

        $readers = new ReaderRegistry([
            new Xliff12Reader($options),
            new Xliff22Reader($options),
        ]);

        return new self($writers, $readers, $options);
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    public function addDocument(string $id, string $sourceLocale, string $targetLocale): Document
    {
        $document = new Document(
            id: $id,
            sourceLocale: $this->normalizeLocale($sourceLocale),
            targetLocale: $this->normalizeLocale($targetLocale),
        );
        $this->package->addDocument($document);

        return $document;
    }

    public function toXml(?string $version = null): string
    {
        $version ??= $this->options->defaultVersion;
        $writer = $this->writers->forVersion($version);
        $packageDoc = $this->buildPackageDocument();

        return $writer->write($packageDoc);
    }

    private function buildPackageDocument(): Package
    {
        //        if ($this->documents === []) {
        //
        //            return new Document(sourceLang: 'und', targetLang: 'und', original: 'package');
        //        }
        //
        //        // Hypothèse: package homogène en locales.
        //        $first = $this->documents[0]->getModel();
        //
        //        $package = new Document(
        //            sourceLang: $first->sourceLang,
        //            targetLang: $first->targetLang,
        //            original: 'package'
        //        );
        //
        //        foreach ($this->documents as $xdoc) {
        //            foreach ($xdoc->getModel()->units as $unit) {
        //                $package->addUnit($unit);
        //            }
        //        }
        //
        //        return $package;
        return new Package();
    }

    private static function normalizeLocale(string $locale): string
    {
        return \str_replace('_', '-', $locale);
    }

    public function readXml(string $xliffXml): void
    {
        $reader = $this->readers->detect($xliffXml);
        $this->package = $reader->read($xliffXml);
    }

    public function getPackage(): Package
    {
        return $this->package;
    }
}
