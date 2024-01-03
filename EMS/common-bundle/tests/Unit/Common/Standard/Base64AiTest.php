<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Standard;

use EMS\CommonBundle\Common\Standard\Base64;
use PHPUnit\Framework\TestCase;

class Base64AiTest extends TestCase
{
    public function testEncode(): void
    {
        $original = 'Hello World';
        $encoded = Base64::encode($original);

        $this->assertEquals(\base64_encode($original), $encoded);
    }

    public function testDecode(): void
    {
        $original = 'Hello World';
        $encoded = \base64_encode($original);
        $decoded = Base64::decode($encoded);

        $this->assertEquals($original, $decoded);
    }

    public function testDecodeInvalidBase64(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid base64 💀');

        Base64::decode('💀');
    }
}
