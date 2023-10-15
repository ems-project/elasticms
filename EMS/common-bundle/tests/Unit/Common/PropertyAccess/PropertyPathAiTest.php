<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\PropertyAccess;

use EMS\CommonBundle\Common\PropertyAccess\InvalidPropertyPathException;
use EMS\CommonBundle\Common\PropertyAccess\PropertyPath;
use PHPUnit\Framework\TestCase;

class PropertyPathAiTest extends TestCase
{
    public function testConstructor(): void
    {
        $propertyPath = new PropertyPath('[operator1:slug1][operator2:slug2]');
        $this->assertSame('[operator1:slug1][operator2:slug2]', $propertyPath->getPathAsString());
    }

    public function testInvalidPropertyPath(): void
    {
        $this->expectException(InvalidPropertyPathException::class);
        new PropertyPath('[operator1:slug1]invalid[operator2:slug2]');
    }

    public function testGetElements(): void
    {
        $propertyPath = new PropertyPath('[operator1:slug1][operator2:slug2]');
        $elements = $propertyPath->getElements();
        $this->assertCount(2, $elements);
        $this->assertSame('slug1', $elements[0]->getName());
        $this->assertSame('slug2', $elements[1]->getName());
    }

    public function testIterator(): void
    {
        $propertyPath = new PropertyPath('[operator1:slug1][operator2:slug2]');
        $elements = [];
        foreach ($propertyPath as $element) {
            $elements[] = $element->getName();
        }
        $this->assertSame(['slug1', 'slug2'], $elements);
    }

    public function testCount(): void
    {
        $propertyPath = new PropertyPath('[operator1:slug1][operator2:slug2]');
        $this->assertCount(2, $propertyPath);
    }

    public function testLast(): void
    {
        $propertyPath = new PropertyPath('[operator1:slug1][operator2:slug2]');
        $this->assertFalse($propertyPath->last());
        $propertyPath->next();
        $this->assertTrue($propertyPath->last());
    }

    public function testSetAndGetIndex(): void
    {
        $propertyPath = new PropertyPath('[operator1:slug1][operator2:slug2]');
        $propertyPath->setIndex(1);
        $this->assertSame(1, $propertyPath->getIndex());
    }

    public function testCurrentWithInvalidIndex(): void
    {
        $propertyPath = new PropertyPath('[operator1:slug1]');
        $propertyPath->setIndex(2);
        $this->expectException(\RuntimeException::class);
        $propertyPath->current();
    }
}
