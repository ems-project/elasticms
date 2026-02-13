<?php

declare(strict_types=1);

namespace EMS\Xliff\Html;

use EMS\Helpers\Html\HtmlHelper;
use EMS\Helpers\Standard\Type;
use EMS\Xliff\Id\IdGeneratorInterface;
use EMS\Xliff\Model\DocumentNodeInterface;
use EMS\Xliff\Model\Inline\Group;
use EMS\Xliff\Model\Inline\Node;
use EMS\Xliff\Model\Inline\PairedCode;
use EMS\Xliff\Model\Inline\Placeholder;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Model\Note;
use EMS\Xliff\Model\Segment;
use EMS\Xliff\Model\Unit;
use EMS\Xliff\Model\UnitGroup;
use EMS\Xliff\XML\DomHelper;
use Symfony\Component\DomCrawler\Crawler;

class HtmlExtractor
{
    private const array INTERNAL_TAGS = [
        'a',
        'abbr',
        'acronym',
        'applet',
        'b',
        'bdo',
        'big',
        'blink',
        'br',
        'button',
        'cite',
        'code',
        'del',
        'dfn',
        'em',
        'embed',
        'face',
        'font',
        'i',
        'iframe',
        'img',
        'input',
        'ins',
        'kbd',
        'label',
        'map',
        'nobr',
        'object',
        'param',
        'q',
        'rb',
        'rbc',
        'rp',
        'rt',
        'rtc',
        'ruby',
        's',
        'samp',
        'select',
        'small',
        'span',
        'spacer',
        'strike',
        'strong',
        'sub',
        'sup',
        'symbol',
        'textarea',
        'tt',
        'u',
        'var',
        'wbr',
    ];

    public function __construct(
        private readonly IdGeneratorInterface $idGenerator,
    ) {
    }

    public function extract(string $fieldPath, string $sourceHtml, ?string $targetHtml = null, ?string $baselineHtml = null, bool $isFinal = false): UnitGroup
    {
        $unitGroup = new UnitGroup(
            id: $this->idGenerator->nextUnitGroupId(),
            resourceName: $fieldPath,
        );
        $isFinal = $isFinal || ($baselineHtml === $sourceHtml);
        $sourceCrawler = new Crawler(HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($sourceHtml)));
        $targetCrawler = new Crawler(HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($targetHtml)));
        $baselineCrawler = new Crawler(HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($baselineHtml)));
        $domHtml = $sourceCrawler->getNode(0);
        if (!$domHtml instanceof \DOMElement) {
            throw new \RuntimeException('Could not extract the source');
        }
        $body = DomHelper::getSingleElement($domHtml, 'body');
        $nodes = $this->addNode($body, $targetCrawler, $baselineCrawler, $isFinal);
        foreach ($nodes as $node) {
            $unitGroup->addNode($node);
        }

        return $unitGroup;
    }

    /**
     * @return DocumentNodeInterface[]
     */
    private function addNode(\DOMNode $sourceNode, Crawler $targetCrawler, Crawler $baselineCrawler, bool $isFinal): array
    {
        $nodes = [];
        $currentInlineSegment = null;
        foreach ($sourceNode->childNodes as $domNode) {
            if ($domNode instanceof \DOMText && $this->isEmpty($domNode)) {
                continue;
            }
            if ($this->isInline($domNode)) {
                $appendable = $this->isAppendableInline($domNode);
                if (null === $currentInlineSegment || !$appendable) {
                    $currentInlineUnit = $this->initInlineUnit($domNode, $isFinal);
                    $nodes[] = $currentInlineUnit;
                    $currentInlineSegment = $currentInlineUnit->getSegments()[0] ?? null;
                    if (!$currentInlineSegment instanceof Segment) {
                        throw new \RuntimeException('Unexpected null inline segment');
                    }
                }
                $this->appendInline($currentInlineSegment, $domNode, $targetCrawler, $baselineCrawler);
                if (!$appendable) {
                    $currentInlineSegment = null;
                }
            } else {
                $currentInlineSegment = null;
                $unit = new UnitGroup(
                    id: $this->idGenerator->nextUnitGroupId(),
                    resourceName: self::getResourceName($domNode),
                    type: $domNode->nodeName,
                );
                $this->nodeAttributesToNotes($unit, $domNode);
                $childNodes = $this->addNode($domNode, $targetCrawler, $baselineCrawler, $isFinal);
                foreach ($childNodes as $node) {
                    $unit->addNode($node);
                }
                $nodes[] = $unit;
            }
        }

        return $nodes;
    }

    private function initInlineUnit(\DOMNode $sourceNode, bool $isFinal): Unit
    {
        $type = null;
        if ($sourceNode instanceof \DOMElement && !\in_array($sourceNode->nodeName, self::INTERNAL_TAGS, true)) {
            $type = $sourceNode->nodeName;
        }
        $unit = new Unit(
            id: $this->idGenerator->nextUnitId(),
            resourceName: $this->getResourceName($sourceNode),
            type: $type,
        );
        $this->nodeAttributesToNotes($unit, $sourceNode);
        $segment = Segment::init(isFinal: $isFinal);
        $unit->addSegment($segment);

        return $unit;
    }

    private function appendInline(Segment $segment, \DOMNode $sourceNode, Crawler $targetCrawler, Crawler $baselineCrawler): void
    {
        $segment->addSourceNodes($this->buildNodes($sourceNode));
        $targetNodes = [];

        $nodeXPath = $this->getXPath($sourceNode);
        $foundTarget = $targetCrawler->filterXPath($nodeXPath);
        $foundTargetNode = $foundTarget->getNode(0);
        if (null !== $foundTargetNode) {
            $targetNodes = $this->buildNodes($foundTargetNode);
        }
        if ([] !== $targetNodes) {
            $segment->addTargetNodes($targetNodes);
        }

        $foundBaseline = $baselineCrawler->filterXPath($nodeXPath);
        $foundBaselineNode = $foundBaseline->getNode(0);
        if (null !== $foundBaselineNode) {
            $segment->addBaseline($sourceNode->textContent, $foundBaselineNode->textContent);
        }
    }

    /**
     * @return Node[]
     */
    private function buildNodes(\DOMNode $node): array
    {
        if ('#text' === $node->nodeName) {
            return [new Text(self::trimUselessWhiteSpaces($node->textContent))];
        }

        if (\in_array($node->nodeName, self::INTERNAL_TAGS, true)) {
            if (!$node->hasChildNodes() && $node instanceof \DOMElement) {
                $placeholder = new Placeholder(
                    id: $this->idGenerator->nextPlaceholderId(),
                    type: $node->nodeName,
                    equivalentText: $node->hasAttributes() ? $this->buildEquivTextOpeningTag($node).$this->buildEquivTextClosingTag($node) : ' ',
                );

                return [$placeholder];
            } elseif ($node->hasAttributes() && $node instanceof \DOMElement) {
                $pairedCode = new PairedCode(
                    referenceId: $this->idGenerator->nextReferenceId(),
                    id: $this->idGenerator->nextInlineCodeId(),
                    endId: $this->idGenerator->nextEndInlineCodeId(),
                    resourceName: $this->getResourceName($node),
                    equivalentOpeningText: $this->buildEquivTextOpeningTag($node),
                    equivalentClosingText: $this->buildEquivTextClosingTag($node),
                );
                for ($i = 0; $i < $node->childNodes->length; ++$i) {
                    $child = $node->childNodes->item($i);
                    if (null === $child) {
                        continue;
                    }
                    $pairedCode->addChildren($this->buildNodes($child));
                }

                return [$pairedCode];
            }
            $group = new Group(
                id: $this->idGenerator->nextGroupId(),
                type: $node->nodeName,
            );
            for ($i = 0; $i < $node->childNodes->length; ++$i) {
                $child = $node->childNodes->item($i);
                if (null === $child) {
                    continue;
                }
                $group->addChildren($this->buildNodes($child));
            }

            return [$group];
        }

        $nodes = [];
        for ($i = 0; $i < $node->childNodes->length; ++$i) {
            $child = $node->childNodes->item($i);
            if (null === $child) {
                continue;
            }
            $nodes = \array_merge($nodes, $this->buildNodes($child));
        }

        return $nodes;
    }

    private function isEmpty(\DOMNode $sourceNode): bool
    {
        $trimmed = $this->trimUselessWhiteSpaces($sourceNode->textContent);
        if ('' === $trimmed) {
            return true;
        }
        if (' ' === $trimmed && $sourceNode->nextSibling instanceof \DOMElement && !\in_array($sourceNode->nextSibling->nodeName, self::INTERNAL_TAGS, true)) {
            return true;
        }
        if (' ' === $trimmed && $sourceNode->previousSibling instanceof \DOMElement && !\in_array($sourceNode->previousSibling->nodeName, self::INTERNAL_TAGS, true)) {
            return true;
        }

        return false;
    }

    private function trimUselessWhiteSpaces(string $text): string
    {
        $trimmed = \preg_replace('!\s+!', ' ', $text);
        if (!\is_string($trimmed)) {
            throw new \RuntimeException('Unexpected non string preg_replace output');
        }

        return $trimmed;
    }

    private function isInline(\DOMNode $sourceNode): bool
    {
        if (!$sourceNode->hasChildNodes()) {
            return true;
        }
        for ($i = 0; $i < $sourceNode->childNodes->length; ++$i) {
            $child = $sourceNode->childNodes->item($i);
            if ($child instanceof \DOMElement && !\in_array($child->nodeName, self::INTERNAL_TAGS, true)) {
                return false;
            }
        }

        return true;
    }

    private function getResourceName(\DOMNode $domNode, ?string $attributeName = null): string
    {
        $resourceName = $domNode->getNodePath();
        if (null === $resourceName) {
            throw new \RuntimeException('Unexpected null DOMNode');
        }
        if (null !== $attributeName) {
            $resourceName = \sprintf('%s[@%s]', $resourceName, $attributeName);
        }

        return $resourceName;
    }

    private function buildEquivTextOpeningTag(\DOMElement $el): string
    {
        $doc = $el->ownerDocument;
        if (null === $doc) {
            throw new \RuntimeException('Unexpected null doc');
        }
        $clone = $doc->createElement($el->tagName);

        foreach ($el->attributes as $attr) {
            $clone->setAttribute($attr->name, $attr->value);
        }
        $xml = $doc->saveXML($clone);
        $xml = \preg_replace('#/>$#', '>', \trim(Type::string($xml)));

        return \htmlspecialchars(Type::string($xml), ENT_QUOTES | ENT_XML1);
    }

    private function buildEquivTextClosingTag(\DOMElement $el): string
    {
        return \htmlspecialchars('</'.$el->tagName.'>', ENT_QUOTES | ENT_XML1);
    }

    private function nodeAttributesToNotes(DocumentNodeInterface $unit, \DOMNode $node): void
    {
        foreach ($node->attributes ?? [] as $value) {
            $notes = new Note(
                text: $value->nodeValue ?? '',
                from: $value->nodeName
            );
            $unit->addNote($notes);
        }
    }

    private function getXPath(\DOMNode $sourceNode): string
    {
        $nodePath = Type::string($sourceNode->getNodePath());

        return \str_replace('/html/', '//', $nodePath);
    }

    private function isAppendableInline(\DOMNode $domNode): bool
    {
        return \in_array($domNode->nodeName, \array_merge(self::INTERNAL_TAGS, ['#text']), true);
    }
}
