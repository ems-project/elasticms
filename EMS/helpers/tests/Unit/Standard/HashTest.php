<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Hash;
use PHPUnit\Framework\TestCase;

class HashTest extends TestCase
{
    public function testHashString(): void
    {
        self::assertSame('8843d7f92416211de9ebb963ff4ce28125932878', Hash::string('foobar'));
        self::assertSame('prefix8843d7f92416211de9ebb963ff4ce28125932878', Hash::string('foobar', 'prefix'));
    }

    public function testHashArray(): void
    {
        self::assertSame('a5e744d0164540d33b1d7ea616c28f2fa97e754a', Hash::array(['foo' => 'bar']));
        self::assertSame('prefixa5e744d0164540d33b1d7ea616c28f2fa97e754a', Hash::array(['foo' => 'bar'], 'prefix'));
    }

    public function testStringHash(): void
    {
        $value = 'test_string';
        $hashedValue = Hash::string($value);

        $this->assertEquals(\sha1($value), $hashedValue);
    }

    public function testStringHashWithPrefix(): void
    {
        $value = 'test_string';
        $prefix = 'prefix_';
        $hashedValue = Hash::string($value, $prefix);

        $this->assertEquals($prefix.\sha1($value), $hashedValue);
    }

    public function testArrayHash(): void
    {
        $value = ['key' => 'value'];
        $hashedValue = Hash::array($value);

        $this->assertEquals(\sha1(\json_encode($value)), $hashedValue);
    }

    public function testArrayHashWithPrefix(): void
    {
        $value = ['key' => 'value'];
        $prefix = 'prefix_';
        $hashedValue = Hash::array($value, $prefix);

        $this->assertEquals($prefix.\sha1(\json_encode($value)), $hashedValue);
    }
}
