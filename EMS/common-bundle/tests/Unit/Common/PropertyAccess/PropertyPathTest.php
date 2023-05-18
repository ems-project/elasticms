<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\PropertyAccess;

use EMS\CommonBundle\Common\PropertyAccess\InvalidPropertyPathException;
use EMS\CommonBundle\Common\PropertyAccess\PropertyPath;
use PHPUnit\Framework\TestCase;

class PropertyPathTest extends TestCase
{
    public function testSimplePaths(): void
    {
        $propertyPath = new PropertyPath('[fr][content][title]');
        $this->assertEquals('fr', $propertyPath->getElements()[0]->getName());
        $this->assertEquals([], $propertyPath->getElements()[0]->getOperators());
        $this->assertEquals('content', $propertyPath->getElements()[1]->getName());
        $this->assertEquals([], $propertyPath->getElements()[1]->getOperators());
        $this->assertEquals('title', $propertyPath->getElements()[2]->getName());
        $this->assertEquals([], $propertyPath->getElements()[2]->getOperators());
        $this->assertEquals('[fr][content][title]', $propertyPath->getPathAsString());
    }

    public function testException1(): void
    {
        $this->expectException(InvalidPropertyPathException::class);
        new PropertyPath('[json:test]smldnkfsdmljf');
    }

    public function testException2(): void
    {
        $this->expectException(InvalidPropertyPathException::class);
        new PropertyPath('[json:test]smldnkfsdmljf[toto]');
    }

    public function testException3(): void
    {
        $this->expectException(InvalidPropertyPathException::class);
        new PropertyPath('empty.foobar');
    }

    public function testWithOperators(): void
    {
        $ropertyPath = new PropertyPath('[base64:json:test]');
        $this->assertEquals(['base64', 'json'], $ropertyPath->getElements()[0]->getOperators());

        $ropertyPath = new PropertyPath('[foobar][0][fr][json:meta][description]');
        $this->assertEquals('json', $ropertyPath->getElements()[3]->getOperators()[0]);
    }

    public function testIterator(): void
    {
        $propertyPath = new PropertyPath('[foobar][0][fr][json:meta][description]');
        $expected = ['foobar', '0', 'fr', 'meta', 'description'];
        foreach ($propertyPath as $index => $element) {
            $this->assertEquals($expected[$index], $element->getName());
        }
    }
}
