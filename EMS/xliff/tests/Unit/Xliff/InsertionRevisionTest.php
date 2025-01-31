<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Xliff\Xliff\InsertionRevision;
use EMS\Xliff\XML\DomHelper;
use PHPUnit\Framework\TestCase;

class InsertionRevisionTest extends TestCase
{
    public function testAttributeGetter(): void
    {
        $sourceFile = \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'ImporterRevision', 'testAttributes_1.2.xlf']);

        $document = new \DOMDocument();
        $document->loadXML(\file_get_contents($sourceFile));
        foreach ($document->getElementsByTagName('file') as $document) {
            $this->forDocument($document, '1.2');
        }

        $sourceFile = \implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'ImporterRevision', 'testAttributes_2.0.xlf']);

        $document = new \DOMDocument();
        $document->loadXML(\file_get_contents($sourceFile));
        foreach ($document->getElementsByTagName('file') as $document) {
            $this->forDocument($document, '2.0');
        }
    }

    private function forDocument(\DOMElement $document, string $version): void
    {
        $nameSpaces = [];
        foreach (['xml'] as $ns) {
            $nameSpaces[$ns] = $document->ownerDocument->lookupNamespaceURI($ns);
        }

        $object = new InsertionRevision($document, $version, $nameSpaces, null, null);
        foreach ($object->getTranslatedFields() as $field) {
            $this->assertNull($object->getAttributeValue($field, 'toto'));
            if (\in_array($field->nodeName, ['trans-unit', 'segment'])) {
                $this->assertEquals('en', $object->getAttributeValue(DomHelper::getSingleElement($field, 'source'), 'xml:lang', 'en'));
                $this->assertNotNull($object->getAttributeValue($field, 'id'));
            }
        }
    }
}
