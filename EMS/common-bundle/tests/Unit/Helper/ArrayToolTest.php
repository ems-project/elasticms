<?php

namespace EMS\CommonBundle\Tests\Unit\Helper;

use EMS\CommonBundle\Helper\ArrayTool;
use PHPUnit\Framework\TestCase;

class ArrayToolTest extends TestCase
{
    /** @var ArrayTool */
    private $arrayTool;

    protected function setUp(): void
    {
        $this->arrayTool = new ArrayTool();
        parent::setUp();
    }

    public function dataProvider(): array
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
     * @dataProvider dataProvider
     */
    public function testNormalizeArray(array $provided, array $expected, int $sortFlags = SORT_REGULAR): void
    {
        $this->arrayTool->normalizeArray($provided, $sortFlags);
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
        self::assertSame($expected, $this->arrayTool->normalizeAndSerializeArray($provided));
    }
}
