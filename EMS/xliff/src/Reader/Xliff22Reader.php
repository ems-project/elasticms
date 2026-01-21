<?php

declare(strict_types=1);

namespace EMS\Xliff\Reader;

use EMS\Xliff\Model\Package;
use EMS\Xliff\Version;
use EMS\Xliff\XML\DomHelper;

class Xliff22Reader implements ReaderInterface
{
    public function __construct()
    {
    }

    public function supports(string $xml): bool
    {
        return \str_contains($xml, Version::V22_VERSION) || \str_contains($xml, Version::V22_NAMESPACE);
    }

    public function read(string $xml): Package
    {
        $dom = DomHelper::loadXml($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('x', Version::V22_NAMESPACE);
        $result = $xpath->query('/x:xliff');
        if (!$result instanceof \DOMNodeList) {
            throw new \RuntimeException('Root <xliff> element not found.');
        }
        $xliffNode = $result->item(0);
        if (!$xliffNode instanceof \DOMElement) {
            throw new \RuntimeException('Root <xliff> element not found.');
        }
        $sourceLocale = $xliffNode->getAttribute('srcLang');
        $targetLocale = $xliffNode->getAttribute('trgLang');
        $package = new Package();
        $package->setLocales($sourceLocale, $targetLocale);

        $result = $xpath->query('/x:xliff/x:file');
        if (!$result) {
            throw new \RuntimeException('Could not read XLIFF.');
        }
        foreach ($result as $file) {
            if (!$file instanceof \DOMElement) {
                throw new \RuntimeException('Wrong <file> node.');
            }
            $id = $file->getAttribute('original');
            $package->addDocument($id);
        }

        return $package;
    }
}
