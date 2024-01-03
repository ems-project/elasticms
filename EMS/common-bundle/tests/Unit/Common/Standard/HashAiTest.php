<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Standard;

use EMS\CommonBundle\Common\Standard\Hash;
use PHPUnit\Framework\TestCase;

class HashAiTest extends TestCase
{
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
