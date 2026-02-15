<?php

declare(strict_types=1);

namespace EMS\Xliff\Writer;

use EMS\Helpers\Standard\Type;
use EMS\Xliff\Model\Document;
use EMS\Xliff\Model\Inline\Node;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Model\Package;
use EMS\Xliff\Model\Unit;
use EMS\Xliff\Model\UnitGroup;
use EMS\Xliff\Options;
use EMS\Xliff\Version;
use EMS\Xliff\XML\DomHelper;

class Xliff22Writer implements WriterInterface
{
    public function __construct(private readonly Options $options)
    {
    }

    public function supportsVersion(string $version): bool
    {
        return Version::V22 === $version;
    }

    public function write(Package $package, string $encoding = 'UTF-8'): string
    {
        $dom = DomHelper::initDocument($this->options->preserveWhitespace, $this->options->formatOutput);
        $dom->encoding = $encoding;
        $xliff = DomHelper::initXliff($dom, Version::V22, Version::V22_NAMESPACE);
        $xliff->setAttribute('srcLang', $package->getSourceLocale());
        $xliff->setAttribute('trgLang', $package->getTargetLocale());

        foreach ($package->getDocuments() as $document) {
            $this->addDocument($xliff, $document);
        }

        return Type::string($dom->saveXML());
    }

    private function addDocument(\DOMElement $xliff, Document $document): void
    {
        $file = DomHelper::createElement($xliff, 'file');
        $metadata = DomHelper::createElement($file, 'metadata');
        $meta = DomHelper::createElement($metadata, 'meta', [
            'type' => 'original',
        ]);
        $meta->nodeValue = $document->id;
        foreach ($document->getNodes() as $node) {
            switch ($node::class) {
                case Unit::class:
                    $this->addUnit($file, $node);
                    break;
                case UnitGroup::class:
                    $this->addUnitGroup();
                    break;
                default:
                    throw new \LogicException('Unsupported document node');
            }
        }
    }

    private function addUnitGroup(): void
    {
    }

    private function addUnit(\DOMElement $file, Unit $unit): void
    {
        $type = 'text' === $unit->getType() ? null : $unit->getType();
        $unitElement = DomHelper::createElement($file, 'unit', [
            'id' => $unit->id,
            'name' => $unit->resourceName,
            'type' => $type,
        ]);

        foreach ($unit->getSegments() as $segment) {
            $segmentElement = DomHelper::createElement($unitElement, 'segment');

            $source = DomHelper::createElement($segmentElement, 'source');
            $this->appendInlineNodes($source, $segment->getSourceNodes());
            $target = DomHelper::createElement($segmentElement, 'target', [
                'state' => $segment->getState(),
            ]);
            if (!empty($segment->getTargetNodes())) {
                $this->appendInlineNodes($target, $segment->getTargetNodes());
            }
        }
    }

    /**
     * @param Node[] $nodes
     */
    private function appendInlineNodes(\DOMElement $parent, array $nodes): void
    {
        foreach ($nodes as $node) {
            if ($node instanceof Text) {
                $parent->textContent .= $node->text;
                continue;
            }

            //            // 2) <g> (group inline)
            //            if ($node instanceof Group) {
            //                $g = $dom->createElement('g');
            //                $g->setAttribute('id', $node->id);
            //
            //                if ($node->ctype !== null) {
            //                    $g->setAttribute('ctype', $node->ctype);
            //                }
            //
            //                // récursif
            //                $this->appendInlineNodes($g, $node->children);
            //
            //                $parent->appendChild($g);
            //                continue;
            //            }
            //
            //            // 3) <x/> (placeholder)
            //            if ($node instanceof Placeholder) {
            //                $x = $dom->createElement('x');
            //                $x->setAttribute('id', $node->id);
            //
            //                if ($node->ctype !== null) {
            //                    $x->setAttribute('ctype', $node->ctype);
            //                }
            //
            //                if ($node->equiv !== null) {
            //                    $x->setAttribute(
            //                        'equiv-text',
            //                        htmlspecialchars($node->equiv, ENT_QUOTES | ENT_XML1, 'UTF-8')
            //                    );
            //                }
            //
            //                $parent->appendChild($x);
            //                continue;
            //            }
            //
            //            // 4) <bx> … </ex> (paired code)
            //            if ($node instanceof PairedCode) {
            //
            //                // <bx/>
            //                $bx = $dom->createElement('bx');
            //                $bx->setAttribute('id', $node->id);
            //
            //                $bx->setAttribute(
            //                    'equiv-text',
            //                    htmlspecialchars($node->startTag, ENT_QUOTES | ENT_XML1, 'UTF-8')
            //                );
            //
            //                if ($node->ctype !== null) {
            //                    $bx->setAttribute('ctype', $node->ctype);
            //                }
            //
            //                $parent->appendChild($bx);
            //
            //                // contenu interne
            //                $this->appendInlineNodes($parent, $node->children);
            //
            //                // <ex/>
            //                $ex = $dom->createElement('ex');
            //                $ex->setAttribute('id', $node->id);
            //
            //                $ex->setAttribute(
            //                    'equiv-text',
            //                    htmlspecialchars($node->endTag, ENT_QUOTES | ENT_XML1, 'UTF-8')
            //                );
            //
            //                $parent->appendChild($ex);
            //                continue;
            //            }

            throw new \RuntimeException(\sprintf('Inline node %s not supported', $node::class));
        }
    }
}
