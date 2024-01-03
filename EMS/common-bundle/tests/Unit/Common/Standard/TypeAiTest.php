<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Standard;

use EMS\CommonBundle\Common\Standard\Type;
use PHPUnit\Framework\TestCase;

class TypeAiTest extends TestCase
{
    public function testStringReturnsString(): void
    {
        $input = 'test';
        $result = Type::string($input);
        $this->assertSame($input, $result);
    }

    public function testStringThrowsExceptionForNonString(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Expect a string got 'integer'");
        Type::string(123);
    }

    public function testIntegerReturnsInteger(): void
    {
        $input = 123;
        $result = Type::integer($input);
        $this->assertSame($input, $result);
    }

    public function testIntegerThrowsExceptionForNonInteger(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Expect an integer got 'string'");
        Type::integer('test');
    }
}
