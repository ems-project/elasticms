<?php

declare(strict_types=1);

namespace EMS\Xliff\Writer;

use EMS\Helpers\Standard\Type;
use EMS\Xliff\Model\Document;
use EMS\Xliff\Model\DocumentNodeInterface;
use EMS\Xliff\Model\Inline\Group;
use EMS\Xliff\Model\Inline\Node;
use EMS\Xliff\Model\Inline\PairedCode;
use EMS\Xliff\Model\Inline\Placeholder;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Model\Package;
use EMS\Xliff\Model\Unit;
use EMS\Xliff\Model\UnitGroup;
use EMS\Xliff\Options;
use EMS\Xliff\Version;
use EMS\Xliff\XML\DomHelper;

class Xliff12Writer implements WriterInterface
{
    final public const array PRE_DEFINED_VALUES = [
        'b' => 'bold',
        'br' => 'lb',
        'caption' => 'caption',
        'fieldset' => 'groupbox',
        'form' => 'dialog',
        'frame' => 'frame',
        'head' => 'header',
        'i' => 'italic',
        'img' => 'image',
        'li' => 'listitem',
        'menu' => 'menu',
        'table' => 'table',
        'td' => 'cell',
        'tfoot' => 'footer',
        'tr' => 'row',
        'u' => 'underlined',
    ];

    public function __construct(private readonly Options $options)
    {
    }

    public function supportsVersion(string $version): bool
    {
        return Version::V12 === $version;
    }

    public function write(Package $package, string $encoding = 'UTF-8'): string
    {
        $dom = DomHelper::initDocument($this->options->preserveWhitespace, $this->options->formatOutput);
        $dom->encoding = $encoding;
        $xliff = DomHelper::initXliff($dom, Version::V12, Version::V12_NAMESPACE);
        foreach ($package->getDocuments() as $document) {
            $this->addDocument($xliff, $package, $document);
        }

        return Type::string($dom->saveXML());
    }

    private function addDocument(\DOMElement $xliff, Package $package, Document $document): void
    {
        $file = DomHelper::createElement($xliff, 'file', [
            'source-language' => $package->getSourceLocale(),
            'original' => $document->id,
            'datatype' => 'database',
            'target-language' => $package->getTargetLocale(),
        ]);
        $body = DomHelper::createSingleElement($file, 'body');
        foreach ($document->getNodes() as $node) {
            switch ($node::class) {
                case Unit::class:
                    $this->addUnit($package, $body, $node);
                    break;
                case UnitGroup::class:
                    $this->addUnitGroup($package, $body, $node);
                    break;
                default:
                    throw new \LogicException('Unsupported document node');
            }
        }
    }

    private function addUnitGroup(Package $package, \DOMElement $parent, UnitGroup $unitGroup): void
    {
        // TODO: Remove == $unitGroup->getType() ==> just to keep legacy order
        if (null === $unitGroup->getType()) {
            $groupElement = DomHelper::createElement($parent, 'group', [
                'resname' => $unitGroup->getResourceName(),
                'id' => $unitGroup->getId(),
            ]);
        } else {
            $groupElement = DomHelper::createElement($parent, 'group', [
                'restype' => $this->getDocumentNodeResourceType($unitGroup),
                'id' => $unitGroup->getId(),
                'resname' => $unitGroup->getResourceName(),
            ]);
        }
        if (null !== $unitGroup->getType()) {
            foreach ($unitGroup->getNotes() as $note) {
                $noteElement = DomHelper::createElement($groupElement, 'note', [
                    'from' => $note->from,
                ]);
                $noteElement->textContent = $note->text;
            }
        }
        foreach ($unitGroup->getNodes() as $node) {
            switch ($node::class) {
                case Unit::class:
                    $this->addUnit($package, $groupElement, $node);
                    break;
                case UnitGroup::class:
                    $this->addUnitGroup($package, $groupElement, $node);
                    break;
                default:
                    throw new \LogicException('Unsupported document node');
            }
        }
        if (null === $unitGroup->getType()) {
            foreach ($unitGroup->getNotes() as $note) {
                $noteElement = DomHelper::createElement($groupElement, 'note', [
                    'from' => $note->from,
                ]);
                $noteElement->textContent = $note->text;
            }
        }
    }

    private function addUnit(Package $package, \DOMElement $parent, Unit $unit): void
    {
        $tu = DomHelper::createElement($parent, 'trans-unit', [
            'restype' => $this->getDocumentNodeResourceType($unit),
            'id' => $unit->getId(),
            'resname' => $unit->getResourceName(),
        ]);
        foreach ($unit->getSegments() as $segment) {
            $source = DomHelper::createElement($tu, 'source', [
                'xml:lang' => $package->getSourceLocale(),
            ]);
            $this->appendInlineNodes($source, $segment->getSourceNodes());
            $target = DomHelper::createElement($tu, 'target', [
                'xml:lang' => $package->getTargetLocale(),
                'state' => $segment->getState(),
            ]);
            if (!empty($segment->getTargetNodes())) {
                $this->appendInlineNodes($target, $segment->getTargetNodes());
            }
        }
        foreach ($unit->getNotes() as $note) {
            $noteElement = DomHelper::createElement($tu, 'note', [
                'from' => $note->from,
            ]);
            $noteElement->textContent = $note->text;
        }
    }

    /**
     * @param Node[] $nodes
     */
    private function appendInlineNodes(\DOMElement $parent, array $nodes): void
    {
        foreach ($nodes as $node) {
            switch ($node::class) {
                case Text::class:
                    if ('' !== $node->text) {
                        $parent->appendChild(new \DOMText($node->text));
                    }
                    break;
                case PairedCode::class:
                    DomHelper::createElement($parent, 'bx', [
                        'id' => $node->id,
                        'rid' => $node->referenceId,
                        'equiv-text' => $node->equivalentOpeningText,
                    ]);
                    $this->appendInlineNodes($parent, $node->getChildren());
                    DomHelper::createElement($parent, 'ex', [
                        'id' => $node->endId,
                        'rid' => $node->referenceId,
                        'equiv-text' => $node->equivalentClosingText,
                    ]);
                    break;
                case Placeholder::class:
                    DomHelper::createElement($parent, 'x', [
                        'id' => $node->id,
                        'ctype' => $this->getInlineResourceType($node->type),
                        'equiv-text' => $node->equivalentText,
                    ]);
                    break;
                case Group::class:
                    $group = DomHelper::createElement($parent, 'g', [
                        'id' => $node->id,
                        'ctype' => $this->getInlineResourceType($node->type),
                    ]);
                    $this->appendInlineNodes($group, $node->getChildren());
                    break;
                default:
                    throw new \RuntimeException(\sprintf('Inline node %s not supported', $node::class));
            }
        }
    }

    public function getInlineResourceType(string $type): string
    {
        return self::PRE_DEFINED_VALUES[$type] ?? \sprintf('x-html-%s', $type);
    }

    private function getDocumentNodeResourceType(DocumentNodeInterface $documentNode): ?string
    {
        if (\in_array($documentNode->getType(), [null, 'text'], true)) {
            return null;
        }

        return $this->getInlineResourceType($documentNode->getType());
    }
}
