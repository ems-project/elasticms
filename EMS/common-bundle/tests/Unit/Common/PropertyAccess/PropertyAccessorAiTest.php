<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\PropertyAccess;

use EMS\CommonBundle\Common\PropertyAccess\PropertyAccessor;
use PHPUnit\Framework\TestCase;

class PropertyAccessorAiTest extends TestCase
{
    private PropertyAccessor $propertyAccessor;

    #[\Override]
    protected function setUp(): void
    {
        $this->propertyAccessor = PropertyAccessor::createPropertyAccessor();
    }

    public function testGetValue(): void
    {
        $array = [
            'name' => 'John',
            'details' => [
                'age' => 30,
                'address' => '123 Street',
            ],
        ];

        $value = $this->propertyAccessor->getValue($array, '[name]');
        $this->assertSame('John', $value);

        $value = $this->propertyAccessor->getValue($array, '[details][age]');
        $this->assertSame(30, $value);
    }

    public function testSetValue(): void
    {
        $array = [
            'name' => 'John',
            'details' => [
                'age' => 30,
            ],
        ];

        $this->propertyAccessor->setValue($array, '[name]', 'Doe');
        $this->assertSame('Doe', $array['name']);

        $this->propertyAccessor->setValue($array, '[details][address]', '456 Avenue');
        $this->assertSame('456 Avenue', $array['details']['address']);
    }

    public function testIterator(): void
    {
        $array = [
            'users' => [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Doe'],
            ],
        ];

        $results = [];
        foreach ($this->propertyAccessor->iterator('[users][*][name]', $array) as $path => $value) {
            $results[$path] = $value;
        }

        $this->assertSame([
            '[users][0][name]' => 'John',
            '[users][1][name]' => 'Doe',
        ], $results);
    }

    public function testEncodeDecodeException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->propertyAccessor->getValue(['data' => 'invalid'], '[json:data]');
    }

    public function testCreatePropertyAccessor(): void
    {
        $accessor1 = PropertyAccessor::createPropertyAccessor();
        $accessor2 = PropertyAccessor::createPropertyAccessor();

        $this->assertSame($accessor1, $accessor2, 'Expected the same instance of PropertyAccessor');
    }

    public function testSetValueWithOperators(): void
    {
        $array = [
            'data' => '{"name":"John"}',
        ];

        $this->propertyAccessor->setValue($array, '[json:data][name]', 'Doe');
        $this->assertSame('{"name":"Doe"}', $array['data']);
    }

    public function testIdPropertyAsArrayKeyException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->propertyAccessor->getValue(['data' => [['name' => 'John']]], '[id_key:data]');
    }

    public function testInvalidOperatorException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->propertyAccessor->getValue(['data' => 'value'], '[invalid:data]');
    }
}
