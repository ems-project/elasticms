<?php

declare(strict_types=1);

namespace EMS\Xliff\XML;

class DomHelper
{
    public static function getStringAttr(\DOMNode $node, string $name): string
    {
        if (null === $node->attributes) {
            throw new \RuntimeException('Unexpected empty attributes');
        }
        $attr = $node->attributes->getNamedItem($name);
        if (!$attr instanceof \DOMAttr) {
            throw new \RuntimeException('Unexpected DOMAttr object');
        }

        return $attr->value;
    }

    public static function getNullStringAttr(\DOMNode $node, string $name): ?string
    {
        if (null === $node->attributes) {
            return null;
        }
        $attr = $node->attributes->getNamedItem($name);
        if (null === $attr) {
            return null;
        }
        if (!$attr instanceof \DOMAttr) {
            throw new \RuntimeException('Unexpected DOMAttr object');
        }

        return $attr->value;
    }

    public static function getSingleNodeFromDocument(\DOMDocument $document, string $tagName): \DOMNode
    {
        $nodeList = $document->getElementsByTagName($tagName);
        if (1 !== $nodeList->count()) {
            throw new \RuntimeException('Unexpected number of single node');
        }
        $document = $nodeList->item(0);
        if (!$document instanceof \DOMNode) {
            throw new \RuntimeException('Unexpected XLIFF type');
        }

        return $document;
    }

    public static function getSingleElement(\DOMElement $element, string $tagName): \DOMElement
    {
        $nodeList = $element->getElementsByTagName($tagName);
        if (1 !== $nodeList->count()) {
            throw new \RuntimeException(\sprintf('Unexpected number of single node: %d', $nodeList->count()));
        }
        $element = $nodeList->item(0);
        if (!$element instanceof \DOMNode) {
            throw new \RuntimeException('Unexpected XLIFF type');
        }

        return $element;
    }

    public static function initDocument(bool $preserveWhiteSpace, bool $formatOutput): \DOMDocument
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = $preserveWhiteSpace;
        $dom->formatOutput = $formatOutput;

        return $dom;
    }

    public static function initXliff(\DOMDocument $dom, string $version, string $namespace): \DOMElement
    {
        $xliff = new \DOMElement('xliff');
        $xliff->setAttribute('xmlns', $namespace);
        $xliff->setAttribute('version', $version);

        $dom->appendChild($xliff);

        return $xliff;
    }

    public static function loadXml(string $xml): \DOMDocument
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ok = @$dom->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        if (!$ok) {
            throw new \RuntimeException('Invalid XLIFF XML.');
        }

        return $dom;
    }

    public static function createSingleElement(\DOMElement $parent, string $tagName): \DOMElement
    {
        foreach ($parent->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->tagName === $tagName) {
                return $child;
            }
        }

        return self::createElement($parent, $tagName);
    }

    /**
     * @param array<string, string|null> $attributes
     */
    public static function createElement(\DOMNode $parent, string $tagName, array $attributes = []): \DOMElement
    {
        $element = new \DOMElement($tagName);
        $parent->appendChild($element);
        foreach ($attributes as $name => $value) {
            if (null === $value) {
                continue;
            }
            $element->setAttribute($name, $value);
        }

        return $element;
    }

    public static function getElement(\DOMXPath $xpath, \DOMElement $element, string $query): \DOMElement
    {
        $result = $xpath->query($query, $element);
        if (!$result instanceof \DOMNodeList) {
            throw new \RuntimeException(\sprintf('Element not found for query %s', $query));
        }
        $node = $result->item(0);
        if (!$node instanceof \DOMElement) {
            throw new \RuntimeException(\sprintf('Element not found for query %s', $query));
        }

        return $node;
    }

    /**
     * @return iterable<\DOMElement>
     */
    public static function elementIterator(\DOMXPath $xpath, \DOMElement $element, string $query): iterable
    {
        $result = $xpath->query($query, $element);
        if (!$result instanceof \DOMNodeList) {
            throw new \RuntimeException(\sprintf('Element not found for query %s', $query));
        }
        foreach ($result as $node) {
            if (!$node instanceof \DOMElement) {
                throw new \RuntimeException(\sprintf('Unexpected non \DOMElement object: %s', $node::class));
            }
            yield $node;
        }
    }

    public static function createElementFromString(\DOMNode $dom, string $html, string $qualifiedName): \DOMElement
    {
        $sourceDom = new \DOMDocument('1.0', 'UTF-8');
        \libxml_use_internal_errors(true);
        $sourceDom->loadHTML(
            "<div>$html</div>",
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        \libxml_clear_errors();
        $element = $sourceDom->getElementsByTagName($qualifiedName)->item(0);
        if (!$element instanceof \DOMElement) {
            throw new \RuntimeException(\sprintf('No <%s> element found', $qualifiedName));
        }
        if (null === $dom->ownerDocument) {
            throw new \RuntimeException('Unexpected null document');
        }
        $imported = $dom->ownerDocument->importNode($element, true);
        if (!$imported instanceof \DOMElement) {
            throw new \RuntimeException('Unexpected error on import node');
        }
        $dom->appendChild($imported);

        return $imported;
    }
}
