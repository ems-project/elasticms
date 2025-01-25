<?php

declare(strict_types=1);

namespace EMS\Xliff\Xliff;

use EMS\Helpers\Html\HtmlHelper;
use Symfony\Component\DomCrawler\Crawler;

class Extractor
{
    // Source: https://docs.oasis-open.org/xliff/v1.2/xliff-profile-html/xliff-profile-html-1.2.html#SectionDetailsElements
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

    private const array TRANSLATABLE_ATTRIBUTES = ['title', 'alt', 'aria-label'];
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

    final public const string XLIFF_1_2 = '1.2';
    final public const string XLIFF_2_0 = '2.0';
    final public const array XLIFF_VERSIONS = [self::XLIFF_1_2, self::XLIFF_2_0];

    private int $nextId = 1;
    private readonly string $xliffVersion;
    private readonly \DOMElement $xliff;
    private readonly \DOMDocument $dom;

    public function __construct(private readonly string $sourceLocale, private readonly ?string $targetLocale = null, string $xliffVersion = self::XLIFF_1_2)
    {
        if (!\in_array($xliffVersion, self::XLIFF_VERSIONS)) {
            throw new \RuntimeException(\sprintf('Unsupported XLIFF version "%s", use one of the supported one: %s', $xliffVersion, \join(', ', self::XLIFF_VERSIONS)));
        }

        $this->nextId = 1;
        $this->xliffVersion = $xliffVersion;

        switch ($xliffVersion) {
            case self::XLIFF_1_2:
                $xliffAttributes = [
                    'xmlns:html' => 'http://www.w3.org/1999/xhtml',
                    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                    'xmlns' => 'urn:oasis:names:tc:xliff:document:'.$xliffVersion,
                    'version' => $xliffVersion,
                    'xsi:schemaLocation' => 'urn:oasis:names:tc:xliff:document:1.2 https://docs.oasis-open.org/xliff/v1.2/os/xliff-core-1.2-strict.xsd',
                ];
                break;
            case self::XLIFF_2_0:
                $xliffAttributes = [
                    'version' => $xliffVersion,
                    'xmlns' => 'urn:oasis:names:tc:xliff:document:'.$xliffVersion,
                    'srcLang' => $sourceLocale,
                ];
                if (null !== $targetLocale) {
                    $xliffAttributes['trgLang'] = $targetLocale;
                }
                break;
            default:
                throw new \RuntimeException('Unexpected XLIFF version');
        }

        $this->dom = new \DOMDocument('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;

        $this->xliff = new \DOMElement('xliff');
        $this->dom->appendChild($this->xliff);
        foreach ($xliffAttributes as $attribute => $value) {
            $this->xliff->setAttribute($attribute, $value);
        }
    }

    public function addDocument(string $contentType, string $ouuid, string $revisionId): \DOMElement
    {
        $id = \join(':', [$contentType, $ouuid, $revisionId]);
        if (\version_compare($this->xliffVersion, '2.0') < 0) {
            $subNode = 'body';
            $documentAttributes = [
                'source-language' => $this->sourceLocale,
                'original' => $id,
                'datatype' => 'database',
            ];
            if (null !== $this->targetLocale) {
                $documentAttributes['target-language'] = $this->targetLocale;
            }
        } else {
            $subNode = null;
            $documentAttributes = [
                'id' => $id,
            ];
        }
        $document = new \DOMElement('file');
        $this->xliff->appendChild($document);
        foreach ($documentAttributes as $attribute => $value) {
            $document->setAttribute($attribute, $value);
        }

        if (null !== $subNode) {
            $subElement = new \DOMElement($subNode);
            $document->appendChild($subElement);

            return $subElement;
        }

        return $document;
    }

    public function saveXML(string $filename, string $encoding = 'UTF-8'): bool
    {
        $this->dom->encoding = $encoding;

        return false !== $this->dom->save($filename);
    }

    public function getDom(): \DOMDocument
    {
        return $this->dom;
    }

    public function addSimpleField(\DOMElement $document, string $fieldPath, string $source, ?string $target = null, bool $isFinal = false): void
    {
        $xliffAttributes = [
            'id' => $fieldPath,
        ];
        if (\version_compare($this->xliffVersion, '2.0') < 0) {
            $qualifiedName = 'trans-unit';
        } else {
            $qualifiedName = 'unit';
        }
        $unit = new \DOMElement($qualifiedName);
        $document->appendChild($unit);
        foreach ($xliffAttributes as $attribute => $value) {
            $unit->setAttribute($attribute, $value);
        }

        $this->addTextSegment($unit, $this->escapeSpecialCharacters($source), null === $target ? null : $this->escapeSpecialCharacters($target), $isFinal);
    }

    public function addHtmlField(\DOMElement $document, string $fieldPath, ?string $sourceHtml, ?string $targetHtml = null, ?string $baselineHtml = null, bool $isFinal = false): void
    {
        $sourceCrawler = new Crawler(HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($sourceHtml)));
        $targetCrawler = new Crawler(HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($targetHtml)));
        $baselineCrawler = new Crawler(HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($baselineHtml)));
        $added = false;
        $group = new \DOMElement('group');
        $document->appendChild($group);
        $group->setAttribute('id', $fieldPath);
        foreach ($sourceCrawler->filterXPath('//body') as $domNode) {
            $this->addNode($group, $domNode, $targetCrawler, $baselineCrawler, $isFinal);
            $added = true;
        }
        if (!$added) {
            $this->addEmptySegmentNode($group);
        }
    }

    private function addNode(\DOMElement $xliffParent, \DOMNode $sourceNode, Crawler $targetCrawler, Crawler $baselineCrawler, bool $isFinal): void
    {
        $currentSegment = null;
        foreach ($sourceNode->childNodes as $domNode) {
            if ($domNode instanceof \DOMText && $this->isEmpty($domNode)) {
                continue;
            }

            if ($this->isSegmentNode($domNode)) {
                $appendable = $this->isAppendableSegment($domNode);
                if (null === $currentSegment || !$appendable) {
                    $currentSegment = $this->initSegment($xliffParent, $domNode);
                }
                $this->appendSegment($currentSegment, $domNode, $targetCrawler, $baselineCrawler, $isFinal);
                if (!$appendable) {
                    $currentSegment = null;
                }
            } else {
                $currentSegment = null;
                if (\version_compare($this->xliffVersion, '2.0') < 0) {
                    $groupAttributes = [];
                    $groupAttributes['restype'] = static::getRestype($domNode->nodeName);
                    if (null !== $domNode->attributes) {
                        foreach ($domNode->attributes as $value) {
                            if (!$value instanceof \DOMAttr) {
                                throw new \RuntimeException('Unexpected attribute object');
                            }
                            if (\in_array($value->nodeName, self::TRANSLATABLE_ATTRIBUTES, true)) {
                                continue;
                            }
                            $groupAttributes['html:'.$value->nodeName] = $value->nodeValue;
                        }
                    }
                } else {
                    $groupAttributes = [];
                }
                $group = new \DOMElement('group');
                $xliffParent->appendChild($group);
                foreach ($groupAttributes as $attribute => $value) {
                    if (null === $value) {
                        throw new \RuntimeException('Unexpected null value');
                    }
                    $group->setAttribute($attribute, $value);
                }
                $this->addId($group, $domNode);
                $this->addNode($group, $domNode, $targetCrawler, $baselineCrawler, $isFinal);
            }
        }
    }

    private function initSegment(\DOMElement $xliffElement, \DOMNode $sourceNode): \DOMElement
    {
        if (\version_compare($this->xliffVersion, '2.0') < 0) {
            $qualifiedName = null;
        } else {
            $qualifiedName = 'unit';
        }
        if (null !== $qualifiedName) {
            $tempElement = new \DOMElement($qualifiedName);
            $xliffElement->appendChild($tempElement);
            $this->addId($tempElement, $sourceNode);
            $xliffElement = $tempElement;
        }

        $attributes = [];
        if (\version_compare($this->xliffVersion, '2.0') < 0) {
            $qualifiedName = 'trans-unit';
            $sourceAttributes = [
                'xml:lang' => $this->sourceLocale,
            ];
            if ($sourceNode instanceof \DOMElement && !\in_array($sourceNode->nodeName, self::INTERNAL_TAGS)) {
                $attributes = [
                    'restype' => static::getRestype($sourceNode->nodeName),
                ];
            }
        } else {
            $qualifiedName = 'segment';
            $sourceAttributes = [];
        }

        if (null !== $sourceNode->attributes && \version_compare($this->xliffVersion, '2.0') < 0) {
            foreach ($sourceNode->attributes as $value) {
                if (!$value instanceof \DOMAttr) {
                    throw new \RuntimeException('Unexpected attribute object');
                }
                $attributes['html:'.$value->nodeName] = $value->nodeValue;
            }
        }

        $segment = new \DOMElement($qualifiedName);
        $xliffElement->appendChild($segment);
        foreach ($attributes as $attribute => $value) {
            if (null === $value) {
                throw new \RuntimeException('Unexpected null value');
            }
            $segment->setAttribute($attribute, $value);
        }

        $this->addId($segment, $sourceNode);
        $source = new \DOMElement('source');
        $segment->appendChild($source);
        foreach ($sourceAttributes as $attribute => $value) {
            $source->setAttribute($attribute, $value);
        }

        $target = new \DOMElement('target');
        $segment->appendChild($target);

        return $segment;
    }

    private function addId(\DOMElement $xliffElement, \DOMNode $domNode, ?string $attributeName = null): void
    {
        $id = $this->getId($domNode, $attributeName);
        $xliffElement->setAttribute('id', $id);
    }

    private function getXPath(\DOMNode $sourceNode): ?string
    {
        $nodePath = $sourceNode->getNodePath();
        if (null === $nodePath) {
            return null;
        }

        return \str_replace('/html/', '//', $nodePath);
    }

    private function addTextSegment(\DOMElement $unit, string $source, ?string $target, bool $isFinal): void
    {
        if (\version_compare($this->xliffVersion, '2.0') < 0) {
            $qualifiedName = null;
            $sourceAttributes = [
                'xml:lang' => $this->sourceLocale,
            ];
        } else {
            $qualifiedName = 'segment';
            $sourceAttributes = [];
        }
        if (null !== $qualifiedName) {
            $unit = $unit->appendChild(new \DOMElement($qualifiedName));
        }
        $sourceChild = new \DOMElement('source', $source);
        $unit->appendChild($sourceChild);
        foreach ($sourceAttributes as $attribute => $value) {
            $sourceChild->setAttribute($attribute, $value);
        }

        $isTranslated = null !== $target && \strlen($target) > 0;
        if (!$isTranslated && 0 === \strlen($source)) {
            $isTranslated = true;
        }
        if (!$isTranslated || null === $target) {
            $targetChild = new \DOMElement('target');
        } else {
            $targetChild = new \DOMElement('target', $target);
        }
        $unit->appendChild($targetChild);
        $this->setTargetAttributes($targetChild, $isFinal, $isTranslated);
    }

    public static function getRestype(string $nodeName): string
    {
        return self::PRE_DEFINED_VALUES[$nodeName] ?? \sprintf('x-html-%s', $nodeName);
    }

    public function getSourceLocale(): string
    {
        return $this->sourceLocale;
    }

    private function getId(\DOMNode $domNode, ?string $attributeName = null): string
    {
        $id = $domNode->getNodePath();
        if (null === $id) {
            $id = \strval($this->nextId++);
        }
        if (null !== $attributeName) {
            $id = \sprintf('%s[@%s]', $id, $attributeName);
        }

        return $id;
    }

    private function trimUselessWhiteSpaces(string $text): string
    {
        $trimmed = \preg_replace('!\s+!', ' ', $text);
        if (!\is_string($trimmed)) {
            throw new \RuntimeException('Unexpected non string preg_replace output');
        }

        return $trimmed;
    }

    private function escapeSpecialCharacters(string $text): string
    {
        return \htmlspecialchars($text, ENT_QUOTES, 'UTF-8', true);
    }

    private function isSegmentNode(\DOMNode $sourceNode): bool
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

    private function appendSegment(\DOMElement $segment, \DOMNode $sourceNode, Crawler $targetCrawler, Crawler $baselineCrawler, bool $isFinal): void
    {
        if (!$segment->hasAttribute('id')) {
            $this->addId($segment, $sourceNode);
        }
        $source = $segment->getElementsByTagName('source')->item(0);
        if (null === $source) {
            throw new \RuntimeException('Unexpected null source');
        }
        $this->fillInline($sourceNode, $source);

        $target = $segment->getElementsByTagName('target')->item(0);
        if (null === $target) {
            throw new \RuntimeException('Unexpected null $target');
        }

        $nodeXPath = $this->getXPath($sourceNode);
        if (null === $nodeXPath) {
            return;
        }

        $foundTarget = $targetCrawler->filterXPath($nodeXPath);
        $foundTargetNode = $foundTarget->getNode(0);

        $isTranslated = 1 === $foundTarget->count();
        if (!$isTranslated && '' === $source->textContent) {
            $isTranslated = true;
        }

        if ($isTranslated && !$isFinal && \in_array($this->isAlreadyTranslated($target, ['final']), [null, true])) {
            $foundBaseline = $baselineCrawler->filterXPath($nodeXPath);
            $foundBaselineNode = $foundBaseline->getNode(0);
            if (null !== $foundBaselineNode) {
                $isFinal = ($sourceNode->textContent === $foundBaselineNode->textContent);
            }
        }

        if ($isTranslated || null === $this->isAlreadyTranslated($target)) {
            $this->setTargetAttributes($target, $isFinal, $isTranslated);
        }

        if (!$isTranslated || null === $foundTargetNode) {
            return;
        }

        $this->fillInline($foundTargetNode, $target);
    }

    private function addEmptySegmentNode(\DOMElement $xliffElement): void
    {
        if (\version_compare($this->xliffVersion, '2.0') < 0) {
            $qualifiedName = 'trans-unit';
            $sourceAttributes = [
                'xml:lang' => $this->sourceLocale,
            ];
        } else {
            $tempElement = new \DOMElement('unit');
            $xliffElement->appendChild($tempElement);
            $xliffElement = $tempElement;
            $qualifiedName = 'segment';
            $sourceAttributes = [];
        }

        $segment = new \DOMElement($qualifiedName);
        $xliffElement->appendChild($segment);

        $source = new \DOMElement('source');
        $segment->appendChild($source);
        $target = new \DOMElement('target');
        $segment->appendChild($target);

        foreach ($sourceAttributes as $attribute => $value) {
            $source->setAttribute($attribute, $value);
        }
        $this->setTargetAttributes($target, true, true);
    }

    private function fillInline(\DOMNode $sourceNode, \DOMElement $source): void
    {
        if ('#text' === $sourceNode->nodeName) {
            $source->appendChild(new \DOMText($this->trimUselessWhiteSpaces($sourceNode->textContent)));

            return;
        }
        if (\in_array($sourceNode->nodeName, self::INTERNAL_TAGS)) {
            $subNode = new \DOMElement('g');
            $source->appendChild($subNode);
            $subNode->setAttribute('ctype', static::getRestype($sourceNode->nodeName));
            foreach ($sourceNode->attributes ?? [] as $value) {
                if (!$value instanceof \DOMAttr) {
                    throw new \RuntimeException('Unexpected attribute object');
                }
                $nodeValue = $value->nodeValue;
                if (null === $nodeValue) {
                    throw new \RuntimeException('Unexpected null node value');
                }
                $subNode->setAttribute('html:'.$value->nodeName, $nodeValue);
            }
        } else {
            $subNode = $source;
        }

        if (!$sourceNode->hasChildNodes()) {
            return;
        }
        for ($i = 0; $i < $sourceNode->childNodes->length; ++$i) {
            $child = $sourceNode->childNodes->item($i);
            if (null === $child) {
                continue;
            }
            $this->fillInline($child, $subNode);
        }
    }

    /**
     * @param string[] $values
     */
    private function isAlreadyTranslated(\DOMElement $targetChild, array $values = ['final', 'needs-translation']): ?bool
    {
        if (\version_compare($this->xliffVersion, '2.0') < 0 && $targetChild->hasAttribute('state')) {
            return \in_array($targetChild->getAttribute('state'), $values);
        }

        return null;
    }

    private function setTargetAttributes(\DOMElement $targetChild, bool $isFinal, bool $isTranslated): void
    {
        if (\version_compare($this->xliffVersion, '2.0') < 0) {
            if (null !== $this->targetLocale) {
                $targetChild->setAttribute('xml:lang', $this->targetLocale);
            }
            if ($isFinal && $isTranslated) {
                $targetChild->setAttribute('state', 'final');
            } elseif ($isTranslated) {
                $targetChild->setAttribute('state', 'needs-translation');
            } else {
                $targetChild->setAttribute('state', 'new');
            }
        }
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

    private function isAppendableSegment(\DOMNode $domNode): bool
    {
        return \in_array($domNode->nodeName, \array_merge(self::INTERNAL_TAGS, ['#text']));
    }
}
