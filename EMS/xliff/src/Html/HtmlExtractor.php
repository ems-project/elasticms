<?php

declare(strict_types=1);

namespace EMS\Xliff\Html;

use EMS\Helpers\Html\HtmlHelper;
use EMS\Helpers\Standard\Type;
use EMS\Xliff\Id\IdGeneratorInterface;
use EMS\Xliff\Model\DocumentNodeInterface;
use EMS\Xliff\Model\Inline\Node;
use EMS\Xliff\Model\Inline\PairedCode;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Model\Segment;
use EMS\Xliff\Model\Unit;
use EMS\Xliff\Model\UnitGroup;
use EMS\Xliff\Xliff;
use Symfony\Component\DomCrawler\Crawler;

class HtmlExtractor
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
        //        private readonly TranslatableAttributes $policy,
        //        private readonly Options $options,
    ) {
    }

    public function extract(string $fieldPath, string $sourceHtml, ?string $targetHtml = null, ?string $baselineHtml = null): UnitGroup
    {
        $unitGroup = new UnitGroup(
            id: $this->idGenerator->nextUnitGroupId(),
            resourceName: $fieldPath,
        );
        $isFinal = $baselineHtml === $sourceHtml;
        $sourceCrawler = new Crawler(HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($sourceHtml)));
        $targetCrawler = new Crawler(HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($targetHtml)));
        $baselineCrawler = new Crawler(HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($baselineHtml)));
        foreach ($sourceCrawler->filterXPath('//body') as $domNode) {
            $nodes = $this->addNode($domNode, $targetCrawler, $baselineCrawler, $isFinal);
            foreach ($nodes as $node) {
                $unitGroup->addNode($node);
            }
        }

        return $unitGroup;
    }

    /**
     * @return DocumentNodeInterface[]
     */
    private function addNode(\DOMNode $sourceNode, Crawler $targetCrawler, Crawler $baselineCrawler, bool $isFinal): array
    {
        $nodes = [];
        //        $currentSegment = null;
        foreach ($sourceNode->childNodes as $domNode) {
            if ($domNode instanceof \DOMText && $this->isEmpty($domNode)) {
                continue;
            }
            if ($this->isInline($domNode)) {
                $nodes[] = $this->addInlineUnit($domNode, $targetCrawler, $baselineCrawler, $isFinal);
            } else {
                $unit = new UnitGroup(
                    id: $this->idGenerator->nextUnitId(),
                    type: self::getResourceType($domNode),
                    resourceName: self::getResourceName($domNode),
                );
                $nodes[] = $unit;
                //                $currentSegment = null;
                //                if (\version_compare($this->xliffVersion, '2.0') < 0) {
                //                    $subGroupAttributes = [];
                //                    $notes = [];
                //                    $subGroupAttributes['restype'] = static::getRestype($domNode->nodeName);
                //                    if (null !== $domNode->attributes) {
                //                        foreach ($domNode->attributes as $value) {
                //                            if (!$value instanceof \DOMAttr) {
                //                                throw new \RuntimeException('Unexpected attribute object');
                //                            }
                //                            if (\in_array($value->nodeName, self::TRANSLATABLE_ATTRIBUTES, true)) {
                //                                continue;
                //                            }
                //                            $notes[$value->nodeName] = $value->nodeValue;
                //                        }
                //                    }
                //                } else {
                //                    $subGroupAttributes = [];
                //                    $notes = [];
                //                }
                //                $subGroup = new \DOMElement('group');
                //                $group->appendChild($subGroup);
                //                foreach ($notes as $from => $value) {
                //                    if (null === $value) {
                //                        throw new \RuntimeException('Unexpected null value');
                //                    }
                //                    $note = new \DOMElement('note');
                //                    $note->setAttribute('from', $from);
                //                    $note->textContent = $value;
                //                    $subGroup->appendChild($note);
                //                }
                //                foreach ($subGroupAttributes as $attribute => $value) {
                //                    $subGroup->setAttribute($attribute, $value);
                //                }
                //                $this->addId($subGroup, $domNode);
                //                $this->addNode($subGroup, $domNode, $targetCrawler, $baselineCrawler, $isFinal);
            }
        }

        return $nodes;
    }

    private function addInlineUnit(\DOMNode $sourceNode, Crawler $targetCrawler, Crawler $baselineCrawler, bool $isFinal): Unit
    {
        $unit = new Unit(
            id: $this->idGenerator->nextUnitId(),
            resourceName: $this->getResourceName($sourceNode),
            type: self::getResourceType($sourceNode),
        );
        $sourceNodes = $this->buildNodes($sourceNode);
        $segment = new Segment($sourceNodes, [], Xliff::STATE_FINAL);
        $unit->addSegment($segment);

        //        $this->fillInline($sourceNode, $source);
        //
        //        $nodeXPath = $this->getXPath($sourceNode);
        //        if (null === $nodeXPath) {
        //            return;
        //        }
        //
        //        $foundTarget = $targetCrawler->filterXPath($nodeXPath);
        //        $foundTargetNode = $foundTarget->getNode(0);
        //
        //        $isTranslated = 1 === $foundTarget->count();
        //        if (!$isTranslated && '' === $source->textContent) {
        //            $isTranslated = true;
        //        }
        //
        //        if ($isTranslated && !$isFinal && \in_array($this->isAlreadyTranslated($target, ['final']), [null, true])) {
        //            $foundBaseline = $baselineCrawler->filterXPath($nodeXPath);
        //            $foundBaselineNode = $foundBaseline->getNode(0);
        //            if (null !== $foundBaselineNode) {
        //                $isFinal = ($sourceNode->textContent === $foundBaselineNode->textContent);
        //            }
        //        }
        //
        //        $this->setTargetAttributes($target, $isFinal, $isTranslated);
        //
        //        if (!$isTranslated || null === $foundTargetNode) {
        //            return;
        //        }
        //
        //        $this->fillInline($foundTargetNode, $target);
        return $unit;
    }

    /**
     * @return Node[]
     */
    private function buildNodes(\DOMNode $node): array
    {
        if ('#text' === $node->nodeName) {
            return [new Text($node->textContent)];
        }

        if (\in_array($node->nodeName, self::INTERNAL_TAGS)) {
            if (!$node->hasChildNodes() && $node instanceof \DOMElement) {
                //                $this->addXNode($node, $source);
                //
                //                return;
            } elseif ($node->hasAttributes() && $node instanceof \DOMElement) {
                $pairedCode = new PairedCode(
                    id: $this->idGenerator->nextInlineCodeId(),
                    endId: $this->idGenerator->nextEndInlineCodeId(),
                    referenceId: $this->idGenerator->nextReferenceId(),
                    resourceName: $this->getResourceName($node),
                    equivalentOpeningText: $this->buildEquivTextOpeningTag($node),
                    equivalentClosingText: $this->buildEquivTextClosingTag($node),
                );
                for ($i = 0; $i < $node->childNodes->length; ++$i) {
                    $child = $node->childNodes->item($i);
                    if (null === $child) {
                        continue;
                    }
                    $pairedCode->addChild($this->buildNodes($child));
                }

                return [$pairedCode];
            } else {
                //                $subNode = new \DOMElement('g');
                //                $subNode->setAttribute('id', \sprintf('g%d', $this->nextId++));
                //                $source->appendChild($subNode);
                //                $subNode->setAttribute('ctype', static::getRestype($node->nodeName));
            }
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
        if (' ' === $trimmed && $sourceNode->nextSibling instanceof \DOMElement && !\in_array($sourceNode->nextSibling->nodeName, self::INTERNAL_TAGS)) {
            return true;
        }
        if (' ' === $trimmed && $sourceNode->previousSibling instanceof \DOMElement && !\in_array($sourceNode->previousSibling->nodeName, self::INTERNAL_TAGS)) {
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
            if ($child instanceof \DOMElement && !\in_array($child->nodeName, self::INTERNAL_TAGS)) {
                return false;
            }
        }

        return true;
    }

    //    private function isAppendableSegment(\DOMNode $domNode): bool
    //    {
    //        return \in_array($domNode->nodeName, \array_merge(self::INTERNAL_TAGS, ['#text']));
    //    }

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

    public static function getResourceType(\DOMNode $node): string
    {
        return self::PRE_DEFINED_VALUES[$node->nodeName] ?? \sprintf('x-html-%s', $node->nodeName);
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
}
