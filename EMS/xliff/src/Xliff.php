<?php

declare(strict_types=1);

namespace EMS\Xliff;

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
        string $sourceLocale,
        string $targetLocale,
        private readonly WriterRegistry $writers,
        private readonly ReaderRegistry $readers,
        private readonly Options $options,
    ) {
        $this->package = new Package();
        $this->package->setLocales($sourceLocale, $targetLocale);
    }

    public static function createDefault(
        string $sourceLocale,
        string $targetLocale,
        ?Options $options = null,
    ): self {
        $options ??= new Options();

        $writers = new WriterRegistry([
            new Xliff12Writer($options),
            new Xliff22Writer($options),
        ]);

        $readers = new ReaderRegistry([
            new Xliff12Reader(),
            new Xliff22Reader(),
        ]);

        return new self($sourceLocale, $targetLocale, $writers, $readers, $options);
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    public function toXml(?string $version = null): string
    {
        $version ??= $this->options->defaultVersion;
        $writer = $this->writers->forVersion($version);

        return $writer->write($this->package);
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
