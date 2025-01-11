<?php

declare(strict_types=1);

namespace EMS\Xliff\Xliff;

use EMS\CommonBundle\Common\PropertyAccess\PropertyAccessor;
use EMS\Helpers\Html\HtmlHelper;
use EMS\Helpers\Standard\Accessor;
use EMS\Xliff\Xliff\Entity\InsertReport;
use EMS\Xliff\XML\DomHelper;

class InsertionRevision
{
    private const string HTML_FIELD = 'html_field';
    private const string SIMPLE_FIELD = 'simple_field';
    private const string UNKNOWN_FIELD_TYPE = 'UNKNOWN_FIELD_TYPE';
    final public const string LOCALE_PLACE_HOLDER = '%locale%';
    private readonly string $contentType;
    private readonly string $ouuid;
    private readonly string $revisionId;

    /**
     * @param string[] $nameSpaces
     */
    public function __construct(private readonly \DOMElement $document, private readonly string $version, private array $nameSpaces, private readonly ?string $sourceLocale, private ?string $targetLocale)
    {
        if (\version_compare($this->version, '2.0') < 0) {
            [$this->contentType, $this->ouuid, $this->revisionId] = \explode(':', DomHelper::getStringAttr($document, 'original'));
        } else {
            [$this->contentType, $this->ouuid, $this->revisionId] = \explode(':', DomHelper::getStringAttr($document, 'id'));
        }
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getOuuid(): string
    {
        return $this->ouuid;
    }

    public function getRevisionId(): string
    {
        return $this->revisionId;
    }

    /**
     * @param array<mixed> $extractedRawData
     * @param array<mixed> $insertRawData
     */
    public function extractTranslations(InsertReport $insertReport, array &$extractedRawData, array &$insertRawData): void
    {
        foreach ($this->getTranslatedFields() as $segment) {
            match ($this->fieldType($segment)) {
                self::HTML_FIELD => $this->importHtmlField($insertReport, $segment, $extractedRawData, $insertRawData),
                self::SIMPLE_FIELD => $this->importSimpleField($insertReport, $segment, $extractedRawData, $insertRawData),
                default => throw new \RuntimeException('Unexpected field type'),
            };
        }
    }

    /**
     * @return \DOMElement[]
     */
    public function getTranslatedFields(): iterable
    {
        if (\version_compare($this->version, '2.0') < 0) {
            $fields = DomHelper::getSingleElement($this->document, 'body');
        } else {
            $fields = $this->document;
        }

        foreach ($fields->childNodes as $child) {
            if (!$child instanceof \DOMElement) {
                continue;
            }
            yield $child;
        }
    }

    private function fieldType(\DOMElement $field): string
    {
        $nodeName = $field->nodeName;
        if ('group' === $nodeName) {
            return self::HTML_FIELD;
        } elseif ('trans-unit' === $nodeName && \version_compare($this->version, '2.0') < 0) {
            return self::SIMPLE_FIELD;
        } elseif ('unit' === $nodeName && \version_compare($this->version, '2.0') >= 0) {
            return self::SIMPLE_FIELD;
        } else {
            return self::UNKNOWN_FIELD_TYPE;
        }
    }

    /**
     * @param array<mixed> $extractedRawData
     * @param array<mixed> $insertRawData
     */
    private function importHtmlField(InsertReport $insertReport, \DOMElement $group, array &$extractedRawData, array &$insertRawData): void
    {
        $sourceValue = $this->getHtml($group, 'source');
        $sourceLocale = $this->sourceLocale;
        foreach ($group->getElementsByTagName('source') as $item) {
            $sourceLocale = $this->getAttributeValue($item, 'xml:lang', $this->sourceLocale);
            break;
        }

        $targetValue = $this->getHtml($group, 'target');
        $targetLocale = $this->targetLocale;
        foreach ($group->getElementsByTagName('target') as $item) {
            $targetLocale = $this->getAttributeValue($item, 'xml:lang', $this->targetLocale);
            break;
        }
        if (null === $targetLocale && null !== $sourceLocale) {
            throw new \RuntimeException('Unexpected missing target locale');
        }

        if (null === $sourceLocale) {
            throw new \RuntimeException('Unexpected missing source locale');
        }
        if (null === $targetLocale) {
            throw new \RuntimeException('Unexpected missing target locale');
        }

        $this->importField($insertReport, $group, $sourceLocale, $targetLocale, $extractedRawData, $sourceValue, $insertRawData, $targetValue, 'html');
    }

    /**
     * @param array<mixed> $extractedRawData
     * @param array<mixed> $insertRawData
     */
    private function importSimpleField(InsertReport $insertReport, \DOMElement $segment, array &$extractedRawData, array &$insertRawData): void
    {
        $source = DomHelper::getSingleElement($segment, 'source');
        $sourceValue = $source->textContent;
        $sourceLocale = $this->getAttributeValue($source, 'xml:lang', $this->sourceLocale);
        if (null === $sourceLocale) {
            throw new \RuntimeException('Unexpected missing source locale');
        }

        $target = DomHelper::getSingleElement($segment, 'target');
        $targetValue = $target->textContent;
        $targetLocale = $this->getAttributeValue($target, 'xml:lang', $this->targetLocale);
        if (null === $targetLocale) {
            throw new \RuntimeException('Unexpected missing target locale');
        }

        $this->importField($insertReport, $segment, $sourceLocale, $targetLocale, $extractedRawData, $sourceValue, $insertRawData, $targetValue, null);
    }

    public function getAttributeValue(\DOMElement $field, string $attributeName, ?string $defaultValue = null): ?string
    {
        if (!\str_contains($attributeName, ':')) {
            $nameSpace = null;
            $tag = $attributeName;
        } else {
            [$nameSpace, $tag] = \explode(':', $attributeName);
        }

        if (null === $nameSpace) {
            if (!$field->hasAttribute($tag)) {
                return $defaultValue;
            }
            $attribute = $field->getAttribute($tag);
        } elseif (!isset($this->nameSpaces[$nameSpace])) {
            return $defaultValue;
        } else {
            if (!$field->hasAttributeNS($this->nameSpaces[$nameSpace], $tag)) {
                return $defaultValue;
            }
            $attribute = $field->getAttributeNS($this->nameSpaces[$nameSpace], $tag);
        }

        return \strval($attribute);
    }

    public function getTargetLocale(): string
    {
        if (null !== $this->targetLocale) {
            return $this->targetLocale;
        }

        $mainDocument = $this->document->ownerDocument;
        if (null === $mainDocument) {
            throw new \RuntimeException('Unexpected null owner document');
        }
        $domXpath = new \DOMXPath($mainDocument);

        if (null === $lookupNamespace = $this->document->lookupNamespaceURI(null)) {
            throw new \RuntimeException('could not find namespace');
        }

        $domXpath->registerNamespace('ns', $lookupNamespace);

        $result = $domXpath->query('//ns:target');
        if (false === $result) {
            throw new \RuntimeException('Unexpected false xpath //ns:target result');
        }
        foreach ($result as $target) {
            if (!$target instanceof \DOMElement) {
                throw new \RuntimeException(\sprintf('Unexpected DOMElement: %s', $target::class));
            }
            if (null === $this->targetLocale) {
                $this->targetLocale = $this->getAttributeValue($target, 'xml:lang');
                continue;
            }
            if ($this->targetLocale !== $this->getAttributeValue($target, 'xml:lang')) {
                throw new \RuntimeException('Elasticms does\'t support XLIFF files containing multiple target languages');
            }
        }

        if (null === $this->targetLocale) {
            throw new \RuntimeException('Unexpected null targetLocale');
        }

        return $this->targetLocale;
    }

    /**
     * @param string[] $namespaces
     */
    private function groupToHtmlNodes(\DOMElement $group, string $nodeName, \DOMElement $parent, array $namespaces, bool $topLevelCall = true): void
    {
        $mainDocument = $group->ownerDocument;
        if (null === $mainDocument) {
            throw new \RuntimeException('Unexpected null owner document');
        }

        $skip = [];
        if ($topLevelCall) {
            $skip = [0, $group->childNodes->length - 1];
        }

        foreach ($group->childNodes as $child) {
            if ($child instanceof \DOMElement && 'group' === $child->nodeName) {
                $tag = $this->restypeToTag(DomHelper::getStringAttr($child, 'restype'));
                $tag = new \DOMElement($tag);
                $parent->appendChild($tag);
                $this->copyHtmlAttribute($child, $tag);
                $this->groupToHtmlNodes($child, $nodeName, $tag, $namespaces, false);
            } elseif ($child instanceof \DOMElement && 'trans-unit' === $child->nodeName) {
                $restype = DomHelper::getNullStringAttr($child, 'restype');
                foreach ($child->childNodes as $grandChild) {
                    if (!$grandChild instanceof \DOMElement || $grandChild->nodeName !== $nodeName) {
                        continue;
                    }
                    if (null === $restype) {
                        $this->rebuildInline($parent, $grandChild);
                        break;
                    }
                    $tag = $this->restypeToTag($restype);
                    $tagDom = new \DOMElement($tag);
                    $parent->appendChild($tagDom);
                    $this->rebuildInline($tagDom, $grandChild);
                    $this->copyHtmlAttribute($child, $tagDom);
                }
            }
        }
    }

    private function getHtml(\DOMElement $group, string $nodeName): string
    {
        $document = $group->ownerDocument;
        if (null === $document) {
            throw new \RuntimeException('Unexpected null document');
        }
        $namespaces = [];
        foreach (['xml'] as $ns) {
            if ($lookupNamespace = $document->lookupNamespaceURI($ns)) {
                $namespaces[$ns] = $lookupNamespace;
            }
        }

        $document = new \DOMDocument();
        $html = new \DOMElement('html');
        $document->appendChild($html);
        $body = new \DOMElement('body');
        $html->appendChild($body);
        $this->groupToHtmlNodes($group, $nodeName, $body, $namespaces);

        $html = '';
        foreach ($body->childNodes as $node) {
            $html .= $document->saveXML($node);
        }

        return HtmlHelper::prettyPrint($html);
    }

    private function restypeToTag(string $restype): string
    {
        $flipped = \array_flip(Extractor::PRE_DEFINED_VALUES);
        if (isset($flipped[$restype])) {
            return $flipped[$restype];
        }
        if (\str_starts_with($restype, 'x-html-')) {
            return \substr($restype, 7);
        }

        throw new \RuntimeException(\sprintf('Unexpected restype %s', $restype));
    }

    private function copyHtmlAttribute(\DOMElement $child, \DOMElement $tag): void
    {
        foreach ($child->attributes ?? [] as $attribute) {
            if (!$attribute instanceof \DOMAttr) {
                throw new \RuntimeException('Unexpected attribute object');
            }
            if ('html' !== $attribute->prefix) {
                continue;
            }
            $attributeLocalName = $attribute->localName;
            if (null === $attributeLocalName) {
                throw new \RuntimeException('Unexpected null attribute local name');
            }
            $tag->setAttribute($attributeLocalName, $attribute->value);
        }
    }

    /**
     * @param mixed[] $extractedRawData
     * @param mixed[] $insertRawData
     */
    private function importField(InsertReport $insertReport, \DOMElement $segment, string $sourceLocale, string $targetLocale, array &$extractedRawData, string $sourceValue, array &$insertRawData, string $targetValue, ?string $format): void
    {
        $propertyPath = Accessor::fieldPathToPropertyPath(DomHelper::getStringAttr($segment, 'id'));
        $sourcePropertyPath = \str_replace(self::LOCALE_PLACE_HOLDER, $sourceLocale, $propertyPath);
        $targetPropertyPath = \str_replace(self::LOCALE_PLACE_HOLDER, $targetLocale, $propertyPath);

        $propertyAccessor = PropertyAccessor::createPropertyAccessor();
        $expectedSourceValue = $propertyAccessor->getValue($extractedRawData, $sourcePropertyPath);

        if ('html' === $format) {
            $expectedSourceValue = HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($expectedSourceValue));
            $sourceValue = HtmlHelper::prettyPrint(HtmlHelper::stripZeroWidthCharacters($sourceValue));
        } elseif (null !== $format) {
            throw new \RuntimeException(\sprintf('Unexpected %s field format', $format));
        }

        $expectedSourceValue ??= '';
        if (\trim((string) $expectedSourceValue) !== \trim($sourceValue)) {
            $insertReport->addError($expectedSourceValue, $sourceValue, $sourcePropertyPath, $this->contentType, $this->ouuid, $this->revisionId);
        }

        $propertyAccessor->setValue($insertRawData, $targetPropertyPath, $targetValue);
    }

    private function rebuildInline(\DOMElement $tagDom, \DOMElement $grandChild): void
    {
        foreach ($grandChild->childNodes as $node) {
            if ($node instanceof \DOMText) {
                $tagDom->appendChild(new \DOMText($node->textContent));
            } elseif ($node instanceof \DOMElement && 'g' === $node->nodeName) {
                $tag = $this->restypeToTag(DomHelper::getStringAttr($node, 'ctype'));
                $tag = new \DOMElement($tag);
                $tagDom->appendChild($tag);
                $this->copyHtmlAttribute($node, $tag);
                $this->rebuildInline($tag, $node);
            }
        }
    }
}
