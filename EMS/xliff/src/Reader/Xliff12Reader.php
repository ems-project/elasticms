<?php

declare(strict_types=1);

namespace EMS\Xliff\Reader;

use EMS\Helpers\Standard\Type;
use EMS\Xliff\Model\Document;
use EMS\Xliff\Model\Inline\Group;
use EMS\Xliff\Model\Inline\Node;
use EMS\Xliff\Model\Inline\Placeholder;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Model\Package;
use EMS\Xliff\Model\Segment;
use EMS\Xliff\Model\Unit;
use EMS\Xliff\Model\UnitGroup;
use EMS\Xliff\Version;
use EMS\Xliff\Writer\Xliff12Writer;
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
        match ($unitElement->nodeName) {
            'trans-unit' => $this->readSimpleText($xpath, $unitElement, $document),
            'group' => $this->readHtml($unitElement, $document),
            default => throw new \RuntimeException(\sprintf('Unexpected node type %s', $unitElement->nodeName)),
        };
    }

    private function readSimpleText(\DOMXPath $xpath, \DOMElement $unitElement, Document $document): void
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

    private function readHtml(\DOMElement $unitElement, Document $document): void
    {
        $unitGroup = $this->readHtmlUnitGroup($unitElement);
        $document->addNode($unitGroup);
    }

    private function readHtmlUnitGroup(\DOMElement $unitElement, ?UnitGroup $parentUnitGroup = null): UnitGroup
    {
        $type = $unitElement->getAttribute('restype');
        $unitGroup = new UnitGroup(
            id: $unitElement->getAttribute('id'),
            resourceName: $unitElement->getAttribute('resname'),
            type: '' === $type ? null : $type,
        );
        foreach ($unitElement->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }
            match ($child->nodeName) {
                'trans-unit' => $this->readHtmlUnit($child, $unitGroup),
                'group' => $this->readHtmlUnitGroup($child, $unitGroup),
                default => throw new \RuntimeException(\sprintf('Unexpected node type %s', $unitElement->nodeName)),
            };
        }
        if (null !== $parentUnitGroup) {
            $parentUnitGroup->addNode($unitGroup);
        }

        return $unitGroup;
    }

    private function readHtmlUnit(\DOMElement $unitElement, UnitGroup $parentUnitGroup): void
    {
        $unitId = $unitElement->getAttribute('id');
        $sourceNodes = [];
        $targetNodes = [];
        $state = null;
        foreach ($unitElement->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }
            match ($child->nodeName) {
                'source' => $sourceNodes = [...$sourceNodes, ...$this->readNode($child)],
                'target' => $targetNodes = [...$targetNodes, ...$this->readNode($child)],
                default => throw new \RuntimeException(\sprintf('Unexpected node type %s', $child->nodeName)),
            };
            if ('target' !== $child->nodeName) {
                continue;
            }
            if (null === $state) {
                $state = $child->getAttribute('state');
            } elseif ($state !== $child->getAttribute('state')) {
                throw new \RuntimeException(\sprintf('Mismatch node type in unit %s', $unitId));
            }
        }
        $segment = Segment::load($sourceNodes, $targetNodes, Type::string($state));
        $type = $unitElement->getAttribute('restype');
        $unit = new Unit(
            id: $unitId,
            resourceName: $unitElement->getAttribute('resname'),
            type: '' === $type ? null : $type,
        );
        $unit->addSegment($segment);
        $parentUnitGroup->addNode($unit);
    }

    /**
     * @return Node[]
     */
    private function readNode(\DOMNode $child): array
    {
        $nodes = [];
        foreach ($child->childNodes as $child) {
            $nodes[] = match ($child->nodeName) {
                '#text' => $this->addTextNode($child),
                'x' => $this->addPlaceholderNode($child),
                'g' => $this->addGroupNode($child),
                default => throw new \RuntimeException(\sprintf('Unexpected node type %s', $child->nodeName)),
            };
        }

        return $nodes;
    }

    private function addTextNode(\DOMNode $child): Text
    {
        return new Text($child->textContent);
    }

    private function addPlaceholderNode(\DOMNode $child): Placeholder
    {
        if (!$child instanceof \DOMElement) {
            throw new \RuntimeException(\sprintf('Unexpected node type %s', $child->nodeName));
        }

        return new Placeholder(
            id: $child->getAttribute('id'),
            type: $this->convertCType($child),
            equivalentText: $child->getAttribute('equiv-text')
        );
    }

    private function addGroupNode(\DOMNode $child): Group
    {
        if (!$child instanceof \DOMElement) {
            throw new \RuntimeException(\sprintf('Unexpected node type %s', $child->nodeName));
        }

        return new Group(
            id: $child->getAttribute('id'),
            type: $this->convertCType($child),
        );
    }

    private function convertCType(\DOMElement $child): string
    {
        $cType = $child->getAttribute('ctype');
        $flipped = \array_flip(Xliff12Writer::PRE_DEFINED_VALUES);
        if (isset($flipped[$cType])) {
            return $flipped[$cType];
        }
        if (\str_starts_with($cType, 'x-html-')) {
            return \substr($cType, 7);
        }

        throw new \RuntimeException(\sprintf('Unexpected restype %s', $cType));
    }
}
