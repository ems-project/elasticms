<?php

declare(strict_types=1);

namespace EMS\Xliff\Reader;

use EMS\Xliff\Model\Package;
use EMS\Xliff\Version;
use EMS\Xliff\XML\DomHelper;

class Xliff12Reader implements ReaderInterface
{
    public function __construct()
    {
    }

    public function supports(string $xml): bool
    {
        return \str_contains($xml, Version::V12_VERSION) || \str_contains($xml, Version::V12_NAMESPACE);
    }

    public function read(string $xml): Package
    {
        $dom = DomHelper::loadXml($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('x', Version::V12_NAMESPACE);

        $package = null;
        $result = $xpath->query('/x:xliff/x:file');
        if (!$result) {
            throw new \RuntimeException('Could not read XLIFF.');
        }
        foreach ($result as $file) {
            if (!$file instanceof \DOMElement) {
                throw new \RuntimeException('Wrong <file> node.');
            }
            $id = $file->getAttribute('original');
            $sourceLocale = $file->getAttribute('source-language');
            $targetLocale = $file->getAttribute('target-language');
            if (null === $package) {
                $package = new Package();
                $package->setLocales($sourceLocale, $targetLocale);
            } elseif ($sourceLocale !== $package->getSourceLocale()) {
                throw new \RuntimeException(\sprintf('source-language mismatch for file %s.', $id));
            } elseif ($targetLocale !== $package->getTargetLocale()) {
                throw new \RuntimeException(\sprintf('target-language mismatch for file %s.', $id));
            }
            $package->addDocument($id);
        }
        if (null === $package) {
            $package = new Package();
        }

        return $package;
    }
}
