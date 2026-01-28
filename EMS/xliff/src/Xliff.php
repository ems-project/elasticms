<?php

declare(strict_types=1);

namespace EMS\Xliff;

use EMS\Helpers\File\File;
use EMS\Xliff\Model\Package;
use EMS\Xliff\Reader\ReaderRegistry;
use EMS\Xliff\Reader\Xliff12Reader;
use EMS\Xliff\Reader\Xliff22Reader;
use EMS\Xliff\Report\InsertReport;
use EMS\Xliff\Writer\WriterRegistry;
use EMS\Xliff\Writer\Xliff12Writer;
use EMS\Xliff\Writer\Xliff22Writer;

final class Xliff
{
    public const string STATE_FINAL = 'final';
    public const string STATE_NEEDS_TRANSLATION = 'needs-translation';
    public const string STATE_NEW = 'new';
    private Package $package;

    private function __construct(
        private readonly WriterRegistry $writers,
        private readonly ReaderRegistry $readers,
        private readonly Options $options,
    ) {
    }

    public static function create(?Options $options = null): self
    {
        $options ??= new Options();

        $writers = new WriterRegistry([
            new Xliff12Writer($options),
            new Xliff22Writer($options),
        ]);

        $readers = new ReaderRegistry([
            new Xliff12Reader(),
            new Xliff22Reader(),
        ]);

        return new self($writers, $readers, $options);
    }

    public function init(string $sourceLocale, string $targetLocale): void
    {
        $this->package = new Package();
        $this->package->setLocales($sourceLocale, $targetLocale);
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    public function toXml(?string $version = null, string $encoding = 'UTF-8'): string
    {
        $version ??= $this->options->defaultVersion;
        $writer = $this->writers->forVersion($version);

        return $writer->write($this->package, $encoding);
    }

    public function saveXml(string $expectedFilename, ?string $version = null, string $encoding = 'UTF-8'): void
    {
        File::putContents($expectedFilename, $this->toXml($version, $encoding));
    }

    public function readXml(string $xliffXml): void
    {
        $insertReport = new InsertReport();
        $reader = $this->readers->detect($xliffXml);
        $this->package = $reader->read($xliffXml, $insertReport);
    }

    public function fromFile(string $filename): void
    {
        $this->readXml(File::fromFilename($filename)->getContents());
    }

    public function getPackage(): Package
    {
        return $this->package;
    }
}
