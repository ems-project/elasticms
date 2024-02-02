<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Hash;
use PHPUnit\Framework\TestCase;

class HashAiTest extends TestCase
{
    public function testStringHash()
    {
        $originalString = 'Test String';
        $hashedString = Hash::string($originalString);
        $this->assertEquals(\sha1($originalString), $hashedString);
    }

    public function testStringHashWithPrefix()
    {
        $originalString = 'Test String';
        $prefix = 'prefix_';
        $hashedString = Hash::string($originalString, $prefix);
        $this->assertEquals($prefix.\sha1($originalString), $hashedString);
    }

    public function testArrayHash()
    {
        $array = ['key' => 'value'];
        $hashedArray = Hash::array($array);
        $this->assertEquals(\sha1(\json_encode($array)), $hashedArray);
    }

    public function testArrayHashWithPrefix()
    {
        $array = ['key' => 'value'];
        $prefix = 'prefix_';
        $hashedArray = Hash::array($array, $prefix);
        $this->assertEquals($prefix.\sha1(\json_encode($array)), $hashedArray);
    }
}
