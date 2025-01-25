<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\ArrayHelper;

use EMS\Helpers\ArrayHelper\ArrayHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ArrayHelperFindTest extends TestCase
{
    public static function provider(): array
    {
        return [
            'test simple find' => ['find', ['a' => 1, 'b' => 2, 'find' => 'test'], ['test']],
            'test recursive find' => ['find', ['a' => 1, 'b' => 2, 'c' => ['c1' => 1, 'c2' => ['find' => 'test']], 'd' => 4], ['test']],
            'test not found' => ['find', ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], []],
            'test duplicate' => ['find', ['a' => ['find' => 'testA'], 'b' => ['find' => 'testB']], ['testA']],
            'test null' => ['find', ['a' => 1, 'b' => 2, 'find' => null], [null]],
        ];
    }

    #[DataProvider('provider')]
    public function testFind($searchValue, array $data, array $expectedResult): void
    {
        $this->assertSame($expectedResult, ArrayHelper::find($searchValue, $data));
    }

    public function testFindString(): void
    {
        $this->assertSame('example', ArrayHelper::findString('test', ['test' => 'example']));
        $this->assertSame(null, ArrayHelper::findString('test', ['test' => null]));

        $this->expectExceptionMessage("Expect a string got 'integer'");
        $this->assertSame('1', ArrayHelper::findString('test', ['test' => 1]));
    }

    public function testFindInteger(): void
    {
        $this->assertSame(999, ArrayHelper::findInteger('test', ['test' => 999]));
        $this->assertSame(null, ArrayHelper::findInteger('test', ['test' => null]));

        $this->expectExceptionMessage("Expect an integer got 'string'");
        $this->assertSame(1, ArrayHelper::findInteger('test', ['test' => '1']));
    }

    public function testFindDateTime(): void
    {
        $dateTime = new \DateTime('now');
        $result = ArrayHelper::findDateTime('date', ['date' => $dateTime->format(\DateTimeInterface::ATOM)]);
        $this->assertSame($dateTime->getTimestamp(), $result->getTimestamp());

        $this->assertSame(null, ArrayHelper::findDateTime('date', ['date' => null]));

        $testFormat = ArrayHelper::findDateTime('date', ['date' => '19/02/1989'], 'd/m/Y');
        $this->assertSame('1989-02-19', $testFormat->format('Y-m-d'));
    }
}
