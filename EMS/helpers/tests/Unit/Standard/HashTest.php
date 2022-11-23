<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Hash;
use PHPUnit\Framework\TestCase;

class HashTest extends TestCase
{
    public function testHashString()
    {
        self::assertSame('8843d7f92416211de9ebb963ff4ce28125932878', Hash::string('foobar'));
        self::assertSame('prefix8843d7f92416211de9ebb963ff4ce28125932878', Hash::string('foobar', 'prefix'));
    }

    public function testHashArray()
    {
        self::assertSame('a5e744d0164540d33b1d7ea616c28f2fa97e754a', Hash::array(['foo' => 'bar']));
        self::assertSame('prefixa5e744d0164540d33b1d7ea616c28f2fa97e754a', Hash::array(['foo' => 'bar'], 'prefix'));
    }
}
