<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Json;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testEncode()
    {
        self::assertSame('{"foo":"\/bar"}', Json::encode(['foo' => '/bar']));
        self::assertSame('{
    "foo": "/bar"
}', Json::encode(['foo' => '/bar'], true));
    }

    public function testEscape()
    {
        self::assertSame("Hello\\\"'\u00e9&\\\\\/'", Json::escape("Hello\"'é&\\/'"));
    }

    public function testDecode()
    {
        self::assertSame(['foo' => '/bar'], Json::decode('{"foo":"\/bar"}'));

        $this->expectException(\RuntimeException::class);
        Json::decode('"foo":"\/bar"}');
    }
}
