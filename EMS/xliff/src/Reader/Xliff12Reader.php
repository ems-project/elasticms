<?php

declare(strict_types=1);

namespace EMS\Xliff\Reader;

use EMS\Helpers\Standard\Type;
use EMS\Xliff\Model\Document;
use EMS\Xliff\Model\DocumentNodeInterface;
use EMS\Xliff\Model\Inline\Group;
use EMS\Xliff\Model\Inline\Node;
use EMS\Xliff\Model\Inline\PairedCode;
use EMS\Xliff\Model\Inline\Placeholder;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Model\Note;
use EMS\Xliff\Model\Package;
use EMS\Xliff\Model\Segment;
use EMS\Xliff\Model\Unit;
use EMS\Xliff\Model\UnitGroup;
use EMS\Xliff\Report\InsertReport;
use EMS\Xliff\Version;
use EMS\Xliff\Writer\Xliff12Writer;
use EMS\Xliff\XML\DomHelper;

class Xliff12Reader implements ReaderInterface
{
    public function supports(string $xml): bool
    {
        return \str_contains($xml, Version::V12_VERSION) || \str_contains($xml, Version::V12_NAMESPACE);
    }

    public function read(string $xml, InsertReport $insertReport): Package
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
                $package = new Package($insertReport);
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
            return new Package($insertReport);
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
            type: $this->convertResourceType($unitElement, 'restype'),
        );
        $document->addNode($unit);
        match ($unit->type) {
            null, 'text' => $this->addText($xpath, $unitElement, $unit),
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
        $unitGroup = new UnitGroup(
            id: $unitElement->getAttribute('id'),
            resourceName: $unitElement->getAttribute('resname'),
            type: $this->convertResourceType($unitElement, 'restype'),
        );
        foreach ($unitElement->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }
            match ($child->nodeName) {
                'trans-unit' => $this->readHtmlUnit($child, $unitGroup),
                'group' => $this->readHtmlUnitGroup($child, $unitGroup),
                'note' => $this->readNote($child, $unitGroup),
                default => throw new \RuntimeException(\sprintf('Unexpected node type %s', $unitElement->nodeName)),
            };
        }
        foreach ($unitElement->attributes as $attribute) {
            if (!\str_starts_with($attribute->nodeName, 'html:')) {
                continue;
            }
            $attributeName = \substr($attribute->nodeName, 5);
            $unitGroup->addNote(new Note(
                text: Type::string($attribute->nodeValue),
                from: $attributeName,
            ));
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
        $unit = new Unit(
            id: $unitId,
            resourceName: $unitElement->getAttribute('resname'),
            type: $this->convertResourceType($unitElement, 'restype'),
        );
        foreach ($unitElement->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }
            match ($child->nodeName) {
                'source' => $sourceNodes = [...$sourceNodes, ...$this->readNode($child)],
                'target' => $targetNodes = [...$targetNodes, ...$this->readNode($child)],
                'note' => $this->readNote($child, $unit),
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
        $unit->addSegment($segment);
        foreach ($unitElement->attributes as $attribute) {
            if (!\str_starts_with($attribute->nodeName, 'html:')) {
                continue;
            }
            $attributeName = \substr($attribute->nodeName, 5);
            $unit->addNote(new Note(
                text: Type::string($attribute->nodeValue),
                from: $attributeName,
            ));
        }
        $parentUnitGroup->addNode($unit);
    }

    /**
     * @return Node[]
     */
    private function readNode(\DOMNode $child): array
    {
        $nodes = [];
        $stackNodes = [];
        $stackPairedCodes = [];
        foreach ($child->childNodes as $child) {
            if ('bx' === $child->nodeName) {
                $pairedCode = $this->addPairedCode($child);
                $stackNodes[$pairedCode->referenceId] = $nodes;
                $stackPairedCodes[$pairedCode->referenceId] = $pairedCode;
                $nodes = [];
            } elseif ('ex' === $child->nodeName) {
                if (!$child instanceof \DOMElement) {
                    throw new \RuntimeException(\sprintf('Unexpected node type %s', $child->nodeName));
                }
                $referenceId = Type::string($child->getAttribute('rid'));
                $pairedCode = $stackPairedCodes[$referenceId] ?? null;
                if (!$pairedCode instanceof PairedCode) {
                    throw new \RuntimeException(\sprintf('Unexpected paired code id %s', $referenceId));
                }
                $pairedCode->addChildren($nodes);
                $nodes = $stackNodes[$referenceId];
                $nodes[] = $pairedCode;
            } else {
                $nodes[] = match ($child->nodeName) {
                    '#text' => $this->addTextNode($child),
                    'x' => $this->addPlaceholderNode($child),
                    'g' => $this->addGroupNode($child),
                    default => throw new \RuntimeException(\sprintf('Unexpected node type %s', $child->nodeName)),
                };
            }
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

        $rawHtml = \html_entity_decode(
            $child->getAttribute('equiv-text'),
            ENT_QUOTES | ENT_XML1,
            'UTF-8'
        );

        return new Placeholder(
            id: $child->getAttribute('id'),
            type: Type::string($this->convertResourceType($child, 'ctype')),
            equivalentText: $rawHtml,
        );
    }

    private function addGroupNode(\DOMNode $child): Group
    {
        if (!$child instanceof \DOMElement) {
            throw new \RuntimeException(\sprintf('Unexpected node type %s', $child->nodeName));
        }

        $type = Type::string($this->convertResourceType($child, 'ctype'));
        $legacyAttributes = '';
        foreach ($child->attributes as $attribute) {
            if (!\str_starts_with($attribute->nodeName, 'html:')) {
                continue;
            }
            $attributeName = \substr($attribute->nodeName, 5);
            $legacyAttributes .= \sprintf(' %s="%s"', $attributeName, $attribute->nodeValue);
        }
        $equivalentOpeningText = null;
        $equivalentClosingText = null;
        if ('' !== $legacyAttributes) {
            $equivalentOpeningText = \sprintf('<%s%s>', $type, $legacyAttributes);
            $equivalentClosingText = \sprintf('</%s>', $type);
        }
        $group = new Group(
            id: $child->getAttribute('id'),
            type: $type,
            equivalentOpeningText: $equivalentOpeningText,
            equivalentClosingText: $equivalentClosingText,
        );
        $group->addChildren($this->readNode($child));

        return $group;
    }

    private function convertResourceType(\DOMElement $child, string $qualifiedName): ?string
    {
        $type = $child->getAttribute($qualifiedName);
        if ('' === $type) {
            return null;
        }
        $flipped = \array_flip(Xliff12Writer::PRE_DEFINED_VALUES);
        if (isset($flipped[$type])) {
            return $flipped[$type];
        }
        if (\str_starts_with($type, 'x-html-')) {
            return \substr($type, 7);
        }

        throw new \RuntimeException(\sprintf('Unexpected restype %s', $type));
    }

    private function addPairedCode(\DOMNode $child): PairedCode
    {
        if (!$child instanceof \DOMElement) {
            throw new \RuntimeException(\sprintf('Unexpected node type %s', $child->nodeName));
        }
        $rawHtml = \html_entity_decode(
            $child->getAttribute('equiv-text'),
            ENT_QUOTES | ENT_XML1,
            'UTF-8'
        );
        if (!\preg_match('/^<\s*(?P<tag>[a-zA-Z][a-zA-Z0-9:-]*)\b[^>]*\/?>$/', $rawHtml, $matches)) {
            throw new \RuntimeException(\sprintf('Unexpected %s html tag', $rawHtml));
        }
        $tag = $matches['tag'];

        return new PairedCode(
            referenceId: $child->getAttribute('rid'),
            id: $child->getAttribute('id'),
            endId: $child->getAttribute('id'),
            resourceName: $tag,
            equivalentOpeningText: $rawHtml,
            equivalentClosingText: \sprintf('</%s>', $tag),
        );
    }

    private function readNote(\DOMElement $child, DocumentNodeInterface $unitGroup): void
    {
        $from = $child->getAttribute('from');
        if ('' === $from) {
            return;
        }
        $unitGroup->addNote(new Note(
            text: $child->textContent,
            from: $from,
        ));
    }
}
