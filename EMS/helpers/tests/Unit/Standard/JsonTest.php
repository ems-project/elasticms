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

    public function testPrettyPrint()
    {
        self::assertSame('{
    "foo": "/bar"
}', Json::prettyPrint('{"foo":"\/bar"}'));
        self::assertSame('{"foo":"\/bar"', Json::prettyPrint('{"foo":"\/bar"'));
        self::assertSame('null', Json::prettyPrint('null'));
    }

    public function normalizeProvider(): array
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

    /**
     * @dataProvider normalizeProvider
     */
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
    public function jsonProvider()
    {
        return [
            'json' => [
                ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5],
                '{"a":1,"b":2,"c":3,"d":4,"e":5}',
            ],
        ];
    }

    /**
     * @dataProvider jsonProvider
     */
    public function testNormalizeAndSerializeArray(array $provided, string $expected): void
    {
        Json::normalize($provided);
        self::assertSame($expected, Json::encode($provided));
    }
}
