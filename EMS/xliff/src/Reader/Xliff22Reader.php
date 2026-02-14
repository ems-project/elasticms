<?php

declare(strict_types=1);

namespace EMS\Xliff\Reader;

use EMS\Xliff\Model\Document;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Model\Package;
use EMS\Xliff\Model\Segment;
use EMS\Xliff\Model\Unit;
use EMS\Xliff\Report\InsertReport;
use EMS\Xliff\Version;
use EMS\Xliff\XML\DomHelper;

class Xliff22Reader implements ReaderInterface
{
    public function supports(string $xml): bool
    {
        return \str_contains($xml, Version::V22_VERSION) || \str_contains($xml, Version::V22_NAMESPACE);
    }

    public function read(string $xml, InsertReport $insertReport): Package
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
        $package = new Package($insertReport);
        $package->setLocales($sourceLocale, $targetLocale);

        $result = $xpath->query('/x:xliff/x:file');
        if (!$result) {
            throw new \RuntimeException('Could not read XLIFF.');
        }
        foreach ($result as $file) {
            if (!$file instanceof \DOMElement) {
                throw new \RuntimeException('Wrong <file> node.');
            }
            $original = DomHelper::getElement($xpath, $file, 'x:metadata/x:meta[@type="original"]');
            $id = $original->textContent;
            $document = $package->addDocument($id);
            foreach (DomHelper::elementIterator($xpath, $file, 'x:unit') as $unitElement) {
                $this->addUnit($xpath, $unitElement, $document);
            }
        }

        return $package;
    }

    private function addUnit(\DOMXPath $xpath, \DOMElement $unitElement, Document $document): void
    {
        $unit = new Unit(
            id: $unitElement->getAttribute('id'),
            resourceName: $unitElement->getAttribute('name'),
            type: $this->convertResourceType($unitElement, 'type'),
        );
        $document->addNode($unit);
        match ($unit->type) {
            null, 'text' => $this->addText($xpath, $unitElement, $unit),
            default => throw new \RuntimeException(\sprintf('Unexpected unit type %s', $unit->type)),
        };
    }

    private function addText(\DOMXPath $xpath, \DOMElement $unitElement, Unit $unit): void
    {
        $sourceElement = DomHelper::getElement($xpath, $unitElement, 'x:segment/x:source');
        $sourceNodes = [];
        if ('' !== $sourceElement->textContent) {
            $sourceNodes[] = new Text($sourceElement->textContent);
        }
        $targetElement = DomHelper::getElement($xpath, $unitElement, 'x:segment/x:target');
        $targetNodes = [];
        if ('' !== $targetElement->textContent) {
            $targetNodes[] = new Text($targetElement->textContent);
        }
        $state = $targetElement->getAttribute('state');
        $segment = Segment::load(
            sourceNodes: $sourceNodes,
            targetNodes: $targetNodes,
            state: $state,
        );
        $unit->addSegment($segment);
    }

    private function convertResourceType(\DOMElement $child, string $qualifiedName): ?string
    {
        $type = $child->getAttribute($qualifiedName);
        if ('' === $type) {
            return null;
        }

        return $type;
    }
}
