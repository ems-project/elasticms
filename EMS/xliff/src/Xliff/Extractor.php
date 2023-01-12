<?php

declare(strict_types=1);

namespace EMS\Xliff\Xliff;

use EMS\Helpers\Html\HtmlHelper;
use Symfony\Component\DomCrawler\Crawler;

class Extractor
{
    // Source: https://docs.oasis-open.org/xliff/v1.2/xliff-profile-html/xliff-profile-html-1.2.html#SectionDetailsElements
    final public const PRE_DEFINED_VALUES = [
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

    private const TRANSLATABLE_ATTRIBUTES = ['title', 'alt', 'aria-label'];
    private const INTERNAL_TAGS = [
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
    final public const XLIFF_1_2 = '1.2';
    final public const XLIFF_2_0 = '2.0';
    final public const XLIFF_VERSIONS = [self::XLIFF_1_2, self::XLIFF_2_0];

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

    public function addHtmlField(\DOMElement $document, string $fieldPath, ?string $sourceHtml, ?string $targetHtml = null, bool $isFinal = false, bool $htmlEncodeInlines = false): void
    {
        $sourceCrawler = new Crawler(HtmlHelper::prettyPrint($sourceHtml));
        $targetCrawler = new Crawler(HtmlHelper::prettyPrint($targetHtml));
        $added = false;
        foreach ($sourceCrawler->filterXPath('//body') as $domNode) {
            $this->addGroupNode($document, $domNode, $targetCrawler, $isFinal, $htmlEncodeInlines, $fieldPath);
            $added = true;
        }
        if (!$added) {
            $group = new \DOMElement('group');
            $document->appendChild($group);
            $group->setAttribute('id', $fieldPath);
            $this->addSegmentNode($group, null, null, true, $htmlEncodeInlines);
        }
    }

    private function addGroupNode(\DOMElement $xliffElement, \DOMNode $sourceNode, Crawler $targetCrawler, bool $isFinal, bool $htmlEncodeInlines = false, ?string $id = null): void
    {
        if ($this->isSegmentNode($sourceNode)) {
            $this->addSegmentNode($xliffElement, $sourceNode, $targetCrawler, $isFinal, $htmlEncodeInlines);

            return;
        }

        if (\version_compare($this->xliffVersion, '2.0') < 0) {
            $groupAttributes = [];
            if (null === $id) {
                $groupAttributes['restype'] = static::getRestype($sourceNode->nodeName);
            }
            if (null !== $sourceNode->attributes) {
                foreach ($sourceNode->attributes as $value) {
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
        $xliffElement->appendChild($group);
        foreach ($groupAttributes as $attribute => $value) {
            if (null === $value) {
                throw new \RuntimeException('Unexpected null value');
            }
            $group->setAttribute($attribute, $value);
        }
        if (null === $id) {
            $this->addId($group, $sourceNode);
        } else {
            $group->setAttribute('id', $id);
        }
        foreach ($sourceNode->childNodes as $childNode) {
            $this->addGroupNode($group, $childNode, $targetCrawler, $isFinal, $htmlEncodeInlines);
        }
    }

    private function addId(\DOMElement $xliffElement, \DOMNode $domNode, string $attributeName = null): void
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
        if (!$sourceNode->childNodes instanceof \DOMNodeList) {
            throw new \RuntimeException(\sprintf('Unexpected %s object, expected \\DOMNodeList', $sourceNode->childNodes::class));
        }
        for ($i = 0; $i < $sourceNode->childNodes->length; ++$i) {
            $child = $sourceNode->childNodes->item($i);
            if ($child instanceof \DOMElement && !\in_array($child->nodeName, self::INTERNAL_TAGS)) {
                return false;
            }
        }

        return true;
    }

    private function addSegmentNode(\DOMElement $xliffElement, ?\DOMNode $sourceNode, ?Crawler $targetCrawler, bool $isFinal, bool $htmlEncodeInlines = false): void
    {
        if (null !== $sourceNode && !$sourceNode instanceof \DOMElement && $this->isEmpty($sourceNode)) {
            return;
        }
        if ((null === $sourceNode || null === $targetCrawler) && $sourceNode !== $targetCrawler) {
            throw new \RuntimeException('Unexpected case where there is a source and no target defined or the opposite');
        }
        if (\version_compare($this->xliffVersion, '2.0') < 0) {
            $qualifiedName = null;
        } else {
            $qualifiedName = 'unit';
        }
        if (null !== $qualifiedName) {
            $tempElement = new \DOMElement($qualifiedName);
            $xliffElement->appendChild($tempElement);
            if (null !== $sourceNode) {
                $this->addId($tempElement, $sourceNode);
            }
            $xliffElement = $tempElement;
        }

        $attributes = [];
        if (\version_compare($this->xliffVersion, '2.0') < 0) {
            $qualifiedName = 'trans-unit';
            $sourceAttributes = [
                'xml:lang' => $this->sourceLocale,
            ];
            if (null !== $sourceNode && $sourceNode instanceof \DOMElement) {
                $attributes = [
                    'restype' => static::getRestype($sourceNode->nodeName),
                ];
            }
        } else {
            $qualifiedName = 'segment';
            $sourceAttributes = [];
        }

        if (null !== $sourceNode && null !== $sourceNode->attributes && \version_compare($this->xliffVersion, '2.0') < 0) {
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

        if (null !== $sourceNode) {
            $this->addId($segment, $sourceNode);
        }

        if ($sourceNode instanceof \DOMText) {
            $source = new \DOMElement('source', $this->trimUselessWhiteSpaces($sourceNode->textContent));
        } else {
            $source = new \DOMElement('source');
        }
        $segment->appendChild($source);

        $target = new \DOMElement('target');
        $segment->appendChild($target);

        foreach ($sourceAttributes as $attribute => $value) {
            $source->setAttribute($attribute, $value);
        }
        if (null === $sourceNode || null === $targetCrawler) {
            $this->setTargetAttributes($target, true, true);

            return;
        }

        if ($htmlEncodeInlines) {
            if ($sourceNode->hasChildNodes()) {
                if (!$sourceNode->childNodes instanceof \DOMNodeList) {
                    throw new \RuntimeException(\sprintf('Unexpected %s object, expected \\DOMNodeList', $sourceNode->childNodes::class));
                }
                for ($i = 0; $i < $sourceNode->childNodes->length; ++$i) {
                    $childNode = $sourceNode->childNodes->item($i);
                    if (null === $childNode || null === $childNode->ownerDocument) {
                        throw new \RuntimeException('Unexpected null object');
                    }
                    $xml = $childNode->ownerDocument->saveXML($childNode);
                    if (false === $xml) {
                        throw new \RuntimeException('Unexpected false xml');
                    }
                    $textValue = \str_replace('&#13;', '', $xml);
                    $source->appendChild(new \DOMText($textValue));
                }
            }
        } else {
            $this->fillInline($sourceNode, $source);
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

        $this->setTargetAttributes($target, $isFinal, $isTranslated);

        if (!$isTranslated || null === $foundTargetNode) {
            return;
        }

        if ($foundTargetNode instanceof \DOMText) {
            $text = new \DOMText($this->trimUselessWhiteSpaces($foundTargetNode->textContent));
            $target->appendChild($text);
        }
        if ($htmlEncodeInlines) {
            if (!$foundTargetNode->hasChildNodes()) {
                return;
            }
            if (!$foundTargetNode->childNodes instanceof \DOMNodeList) {
                throw new \RuntimeException(\sprintf('Unexpected %s object, expected \\DOMNodeList', $foundTargetNode->childNodes::class));
            }
            for ($i = 0; $i < $foundTargetNode->childNodes->length; ++$i) {
                $childNode = $foundTargetNode->childNodes->item($i);
                if (null === $childNode || null === $childNode->ownerDocument) {
                    throw new \RuntimeException('Unexpected null object');
                }
                $xml = $childNode->ownerDocument->saveXML($childNode);
                if (false === $xml) {
                    throw new \RuntimeException('Unexpected false xml');
                }
                $target->appendChild(new \DOMText($xml));
            }
        } else {
            $this->fillInline($foundTargetNode, $target);
        }
    }

    private function fillInline(\DOMNode $sourceNode, \DOMElement $source): void
    {
        if (!$sourceNode->hasChildNodes()) {
            return;
        }
        for ($i = 0; $i < $sourceNode->childNodes->length; ++$i) {
            $child = $sourceNode->childNodes->item($i);
            if ($child instanceof \DOMElement) {
                $subNode = new \DOMElement('g');
                $source->appendChild($subNode);
                $subNode->setAttribute('ctype', static::getRestype($child->nodeName));
                foreach ($child->attributes ?? [] as $value) {
                    if (!$value instanceof \DOMAttr) {
                        throw new \RuntimeException('Unexpected attribute object');
                    }
                    $nodeValue = $value->nodeValue;
                    if (null === $nodeValue) {
                        throw new \RuntimeException('Unexpected null node value');
                    }
                    $subNode->setAttribute('html:'.$value->nodeName, $nodeValue);
                }
                $this->fillInline($child, $subNode);
            } elseif ($child instanceof \DOMText) {
                if ($this->isEmpty($child)) {
                    continue;
                }
                $source->appendChild(new \DOMText($this->trimUselessWhiteSpaces($child->textContent)));
            }
        }
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
}
