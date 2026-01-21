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

    public static function initDocument(string $version): \DOMDocument
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $xliff = new \DOMElement('xliff');
        $xliff->setAttribute('xmlns', \sprintf('urn:oasis:names:tc:xliff:document:%s', $version));
        $xliff->setAttribute('version', $version);
        $dom->appendChild($xliff);

        return $dom;
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
}
