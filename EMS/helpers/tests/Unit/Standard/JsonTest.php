<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Json;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function testPrettyPrint()
    {
        self::assertSame('{
    "foo": "/bar"
}', Json::prettyPrint('{"foo":"\/bar"}'));
        self::assertSame('{"foo":"\/bar"', Json::prettyPrint('{"foo":"\/bar"'));
        self::assertSame('null', Json::prettyPrint('null'));
    }

    public static function normalizeProvider(): array
    {
        return [
            'shouldReturn0' => [
                ['0'], [0 => '0'],
            ],
            'testSorting' => [
                [3 => 'testC', 2 => 'testB', 1 => 'testA'],
                [1 => 'testA', 2 => 'testB', 3 => 'testC'],
                SORT_NUMERIC,
            ],
            'testEmptyArrayShouldBeRemove' => [
                [3 => 'testC', 2 => 'testB', 1 => 'testA', 4 => []],
                [1 => 'testA', 2 => 'testB', 3 => 'testC'],
            ],
            'testNestedEmptyArrays' => [
                [3 => 'testC', 2 => 'testB', 1 => 'testA', 4 => [[]]],
                [1 => 'testA', 2 => 'testB', 3 => 'testC'],
            ],
        ];
    }

    #[DataProvider('normalizeProvider')]
    public function testNormalize(array $provided, array $expected): void
    {
        Json::normalize($provided);
        self::assertSame($expected, $provided);
    }

    /**
     * format: [text,text].
     *
     * @return array
     */
    public static function jsonProvider()
    {
        return [
            'json' => [
                ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5],
                '{"a":1,"b":2,"c":3,"d":4,"e":5}',
            ],
        ];
    }

    #[DataProvider('jsonProvider')]
    public function testNormalizeAndSerializeArray(array $provided, string $expected): void
    {
        Json::normalize($provided);
        self::assertSame($expected, Json::encode($provided));
    }

    public function testIsJson(): void
    {
        $this->assertTrue(Json::isJson('null'));
        $this->assertTrue(Json::isJson('54'));
        $this->assertTrue(Json::isJson('"Foobar"'));
        $this->assertTrue(Json::isJson('{"foo":"bar"}'));
        $this->assertTrue(Json::isJson('[{"foo":"bar"},{"foo":"bar"}]'));
    }

    public function testIsNotJson(): void
    {
        $this->assertFalse(Json::isJson('FOOBAR'));
        $this->assertFalse(Json::isJson('Foobar'));
        $this->assertFalse(Json::isJson('{"foo":"bar"'));
        $this->assertFalse(Json::isJson('[{"foo":"bar"}{"toto":"tata"}]'));
    }

    public function testIsEmptyJson(): void
    {
        $this->assertFalse(Json::isEmpty(\json_encode('FOOBAR')));
        $this->assertFalse(Json::isEmpty(\json_encode(2)));
        $this->assertFalse(Json::isEmpty(\json_encode('{"foo":"bar"')));
        $this->assertFalse(Json::isEmpty(\json_encode('[{"foo":"bar"}{"toto":"tata"}]')));

        $this->assertTrue(Json::isEmpty(\json_encode(0)));
        $this->assertTrue(Json::isEmpty(\json_encode(false)));
        $this->assertTrue(Json::isEmpty(\json_encode(null)));
        $this->assertTrue(Json::isEmpty("\n\t"));
        $this->assertTrue(Json::isEmpty(''));
        $this->assertTrue(Json::isEmpty('       '));
    }

    public function testMixedDecode(): void
    {
        $this->assertEquals(2, Json::mixedDecode('2'));
        $this->assertEquals(2.56, Json::mixedDecode('2.56'));
        $this->assertEquals(true, Json::mixedDecode('true'));
        $this->assertEquals(false, Json::mixedDecode('false'));
        $this->assertEquals('foobar', Json::mixedDecode('"foobar"'));
        $this->assertEquals(null, Json::mixedDecode('null'));
    }

    public function testUnescapeUnicode(): void
    {
        $this->assertEquals('{"A":"éèàçï"}', Json::encode([
            'A' => 'éèàçï',
        ], false, true));
        $this->assertEquals('{"A":"\u00e9\u00e8\u00e0\u00e7\u00ef"}', Json::encode([
            'A' => 'éèàçï',
        ]));
        $this->assertEquals('{
    "A": "\u00e9\u00e8\u00e0\u00e7\u00ef"
}', Json::encode([
            'A' => 'éèàçï',
        ], true));
        $this->assertEquals('{
    "A": "éèàçï"
}', Json::encode([
            'A' => 'éèàçï',
        ], true, true));
    }
}
