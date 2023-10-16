<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Elasticsearch\Document;

use EMS\CommonBundle\Elasticsearch\Document\Document;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    public function testFromArray(): void
    {
        $document = Document::fromArray([
            '_id' => 'ouuid',
            '_index' => 'index',
            '_source' => [
                '_contenttype' => 'content-type',
            ],
        ]);
        $this->assertEquals($document->getContentType(), 'content-type');
        $this->assertEquals($document->getIndex(), 'index');
    }

    public function testGetValue(): void
    {
        $document = Document::fromArray([
            '_id' => 'ouuid',
            '_index' => 'index',
            '_source' => [
                '_contenttype' => 'content-type',
                'foo' => 'bar',
                'nested' => [
                    'field' => [
                        'with_value' => 12,
                    ],
                ],
                'table' => [
                    [
                        'foo' => 'bar',
                    ],
                    [
                        [
                            [
                                false,
                                ['foo' => 'bar'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertEquals('bar', $document->getValue('foo'));
        $this->assertEquals(null, $document->getValue('bar'));
        $this->assertEquals('foo', $document->getValue('bar', 'foo'));
        $this->assertEquals(12, $document->getValue('nested.field.with_value'));
        $this->assertEquals('bar', $document->getValue('table.0.foo'));
        $this->assertEquals('bar', $document->getValue('table[0].foo'));
        $this->assertEquals(false, $document->getValue('table[1][0][0][0]'));
        $this->assertEquals('bar', $document->getValue('table[1][0][0][1].foo'));
        $this->assertEquals('not-found', $document->getValue('table[1][0][20][1].foo', 'not-found'));
    }

    public function testFieldPathToPropertyPath(): void
    {
        $this->assertEquals('[foo]', Document::fieldPathToPropertyPath('foo'));
        $this->assertEquals('[foo][0][45][toto]', Document::fieldPathToPropertyPath('foo[0][45].toto'));
        $this->assertEquals('[foo][0][45][toto]', Document::fieldPathToPropertyPath('foo.0.45.toto'));
        $this->assertEquals('[foo][0][45][toto]', Document::fieldPathToPropertyPath('foo[0].45.toto'));
        $this->assertEquals('[foo]', Document::fieldPathToPropertyPath('[foo]'));
        $this->assertEquals('[foo][0][45][toto]', Document::fieldPathToPropertyPath('[foo][0][45][toto]'));
    }
}
