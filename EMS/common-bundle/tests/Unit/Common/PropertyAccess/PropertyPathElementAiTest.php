<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\PropertyAccess;

use EMS\CommonBundle\Common\PropertyAccess\PropertyPathElement;
use PHPUnit\Framework\TestCase;

class PropertyPathElementAiTest extends TestCase
{
    public function testGetName(): void
    {
        $propertyPathElement = new PropertyPathElement('testName', ['operator1', 'operator2']);
        $this->assertSame('testName', $propertyPathElement->getName());
    }

    public function testGetOperators(): void
    {
        $propertyPathElement = new PropertyPathElement('testName', ['operator1', 'operator2']);
        $this->assertSame(['operator1', 'operator2'], $propertyPathElement->getOperators());
    }

    public function testGetOperatorsAsString(): void
    {
        $propertyPathElement = new PropertyPathElement('testName', ['operator1', 'operator2']);
        $this->assertSame('operator1:operator2:', $propertyPathElement->getOperatorsAsString());

        $propertyPathElementEmpty = new PropertyPathElement('testName', []);
        $this->assertSame('', $propertyPathElementEmpty->getOperatorsAsString());
    }
}
