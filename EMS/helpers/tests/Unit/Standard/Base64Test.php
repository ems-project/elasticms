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
}
