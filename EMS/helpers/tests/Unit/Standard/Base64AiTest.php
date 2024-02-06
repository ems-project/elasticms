<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Base64;
use PHPUnit\Framework\TestCase;

class Base64AiTest extends TestCase
{
    public function testEncode()
    {
        $originalString = 'Test String';
        $encodedString = Base64::encode($originalString);
        $this->assertEquals(\base64_encode($originalString), $encodedString);
    }

    public function testDecode()
    {
        $encodedString = \base64_encode('Test String');
        $decodedString = Base64::decode($encodedString);
        $this->assertEquals('Test String', $decodedString);
    }

    public function testDecodeThrowsRuntimeExceptionOnInvalidBase64()
    {
        $this->expectException(\RuntimeException::class);
        Base64::decode('@');
    }
}
