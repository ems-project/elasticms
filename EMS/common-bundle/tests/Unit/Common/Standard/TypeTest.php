<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Standard;

use EMS\CommonBundle\Common\Standard\Type;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    public function providerString(): array
    {
        return [
            ['test', null],
            [true, "Expect a string got 'boolean'"],
            [1, "Expect a string got 'integer'"],
            [5.6, "Expect a string got 'double'"],
            [['test'], "Expect a string got 'array'"],
            [new \stdClass(), "Expect a string got 'object'"],
            [null, "Expect a string got 'NULL'"],
        ];
    }

    /**
     * @dataProvider providerString
     */
    public function testTypeString($value, string $error = null): void
    {
        if ($error) {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage($error);
        }

        $this->assertEquals($value, Type::string($value));
    }

    public function providerInteger(): array
    {
        return [
            [99, null],
            [true, "Expect an integer got 'boolean'"],
            ['test', "Expect an integer got 'string'"],
            [5.6, "Expect an integer got 'double'"],
            [['test'], "Expect an integer got 'array'"],
            [new \stdClass(), "Expect an integer got 'object'"],
            [null, "Expect an integer got 'NULL'"],
        ];
    }

    /**
     * @dataProvider providerInteger
     */
    public function testTypeInteger($value, string $error = null): void
    {
        if ($error) {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage($error);
        }

        $this->assertEquals($value, Type::integer($value));
    }
}
