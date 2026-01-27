<?php

declare(strict_types=1);

namespace EMS\Xliff\Reader;

use EMS\Xliff\Html\HtmlExtractor;
use EMS\Xliff\Html\HtmlInjector;
use EMS\Xliff\Model\Document;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Model\Package;
use EMS\Xliff\Model\Segment;
use EMS\Xliff\Model\Unit;
use EMS\Xliff\Model\UnitGroup;
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
            $document = $package->addDocument($id);
            foreach (DomHelper::getSingleElement($file, 'body')->childNodes as $child) {
                if (!$child instanceof \DOMElement) {
                    continue;
                }
                $this->addNode($xpath, $child, $document);
            }
        }
        if (null === $package) {
            $package = new Package();
        }

        return $package;
    }

    private function addNode(\DOMXPath $xpath, \DOMElement $unitElement, Document $document): void
    {
        match($unitElement->nodeName) {
            'trans-unit' => $this->addSimpleTextUnit($xpath, $unitElement, $document),
            'group' => $this->addHtmlUnit($xpath, $unitElement, $document),
            default => throw new \RuntimeException(\sprintf('Unexpected node type %s', $unitElement->nodeName)),
        };
    }

    private function addHtmlUnit(\DOMXPath $xpath, \DOMElement $unitElement, Document $document): UnitGroup
    {
        $type = $unitElement->getAttribute('restype');
        $unitGroup = new UnitGroup(
            id: $unitElement->getAttribute('id'),
            resourceName: $unitElement->getAttribute('resname'),
            type: '' === $type ? null : $type,
        );
        $document->addNode($unitGroup);

        return $unitGroup;
    }

    private function addSimpleTextUnit(\DOMXPath $xpath, \DOMElement $unitElement, Document $document): void
    {
        $unit = new Unit(
            id: $unitElement->getAttribute('id'),
            resourceName: $unitElement->getAttribute('resname'),
            type: $unitElement->getAttribute('restype'),
        );
        $document->addNode($unit);
        match ($unit->type) {
            '', 'text' => $this->addText($xpath, $unitElement, $unit),
            default => throw new \RuntimeException(\sprintf('Unexpected unit type %s', $unit->type)),
        };
    }

    private function addText(\DOMXPath $xpath, \DOMElement $unitElement, Unit $unit): void
    {
        $sourceElement = DomHelper::getElement($xpath, $unitElement, 'x:source');
        $source = new Text($sourceElement->textContent);
        $targetElement = DomHelper::getElement($xpath, $unitElement, 'x:target');
        $targetNodes = [];
        if ('' !== $targetElement->textContent) {
            $targetNodes[] = new Text($targetElement->textContent);
        }
        $state = $targetElement->getAttribute('state');
        $segment = Segment::load(
            sourceNodes: [$source],
            targetNodes: $targetNodes,
            state: $state,
        );
        $unit->addSegment($segment);
    }
}
