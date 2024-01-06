<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Base64;
use PHPUnit\Framework\TestCase;

class Base64Test extends TestCase
{
    public function testEncode()
    {
        self::assertSame('Zm9vYmFy', Base64::encode('foobar'));
    }

    public function testDecode()
    {
        self::assertSame('foobar', Base64::decode('Zm9vYmFy'));
    }

    public function testEncode2(): void
    {
        $original = 'Hello World';
        $encoded = Base64::encode($original);

        $this->assertEquals(\base64_encode($original), $encoded);
    }

    public function testDecode2(): void
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
