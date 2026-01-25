<?php

declare(strict_types=1);

namespace EMS\Xliff\Writer;

use EMS\Helpers\Standard\Type;
use EMS\Xliff\Html\HtmlExtractor;
use EMS\Xliff\Id\SequentialIdGenerator;
use EMS\Xliff\Model\Document;
use EMS\Xliff\Model\Inline\Node;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Model\Package;
use EMS\Xliff\Model\Unit;
use EMS\Xliff\Model\UnitGroup;
use EMS\Xliff\Options;
use EMS\Xliff\Version;
use EMS\Xliff\XML\DomHelper;

class Xliff12Writer implements WriterInterface
{
    public function __construct(private readonly Options $options)
    {
        $extractor = new HtmlExtractor(new SequentialIdGenerator());
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
        foreach ($document->getNodes() as $node) {
            switch ($node::class) {
                case Unit::class:
                    $this->addUnit($package, $file, $node);
                    break;
                case UnitGroup::class:
                    $this->addUnitGroup($package, $file, $node);
                    break;
                default:
                    throw new \LogicException('Unsupported document node');
            }
        }
    }

    private function addUnitGroup(Package $package, \DOMElement $file, UnitGroup $unitGroup): void
    {
        // TODO
    }

    private function addUnit(Package $package, \DOMElement $file, Unit $unit): void
    {
        $body = DomHelper::createSingleElement($file, 'body');
        $type = 'text' === $unit->getType() ? null : $unit->getType();
        $tu = DomHelper::createElement($body, 'trans-unit', [
            'id' => $unit->getId(),
            'resname' => $unit->getResourceName(),
            'restype' => $type,
        ]);
        foreach ($unit->getSegments() as $segment) {
            $source = DomHelper::createElement($tu, 'source', [
                'xml:lang' => $package->getSourceLocale(),
            ]);
            $this->appendInlineNodes($source, $segment->sourceNodes);
            $target = DomHelper::createElement($tu, 'target', [
                'xml:lang' => $package->getTargetLocale(),
                'state' => $segment->state,
            ]);
            if (!empty($segment->targetNodes)) {
                $this->appendInlineNodes($target, $segment->targetNodes);
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
                if ('' !== $node->text) {
                    $parent->textContent .= $node->text;
                }
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

            throw new \RuntimeException(\sprintf('Inline node %s not supported', \get_class($node)));
        }
    }
}
