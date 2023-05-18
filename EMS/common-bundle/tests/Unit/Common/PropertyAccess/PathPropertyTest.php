<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\PropertyAccess;

use EMS\CommonBundle\Common\PropertyAccess\InvalidPathPropertyException;
use EMS\CommonBundle\Common\PropertyAccess\PathProperty;
use PHPUnit\Framework\TestCase;

class PathPropertyTest extends TestCase
{
    public function testSimplePaths(): void
    {
        $pathProperty = new PathProperty('[fr][content][title]');
        $this->assertEquals('fr', $pathProperty->getElements()[0]->getName());
        $this->assertEquals([], $pathProperty->getElements()[0]->getOperators());
        $this->assertEquals('content', $pathProperty->getElements()[1]->getName());
        $this->assertEquals([], $pathProperty->getElements()[1]->getOperators());
        $this->assertEquals('title', $pathProperty->getElements()[2]->getName());
        $this->assertEquals([], $pathProperty->getElements()[2]->getOperators());
        $this->assertEquals('[fr][content][title]', $pathProperty->getPathAsString());
    }

    public function testException1(): void
    {
        $this->expectException(InvalidPathPropertyException::class);
        new PathProperty('[json:test]smldnkfsdmljf');
    }

    public function testException2(): void
    {
        $this->expectException(InvalidPathPropertyException::class);
        new PathProperty('[json:test]smldnkfsdmljf[toto]');
    }

    public function testException3(): void
    {
        $this->expectException(InvalidPathPropertyException::class);
        new PathProperty('empty.foobar');
    }

    public function testWithOperators(): void
    {
        $pathProperty = new PathProperty('[base64:json:test]');
        $this->assertEquals(['base64', 'json'], $pathProperty->getElements()[0]->getOperators());

        $pathProperty = new PathProperty('[foobar][0][fr][json:meta][description]');
        $this->assertEquals('json', $pathProperty->getElements()[3]->getOperators()[0]);
    }
}
