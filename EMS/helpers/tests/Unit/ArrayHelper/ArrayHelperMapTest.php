<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\ArrayHelper;

use EMS\Helpers\ArrayHelper\ArrayHelper;
use PHPUnit\Framework\TestCase;

class ArrayHelperMapTest extends TestCase
{
    public function testMap(): void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        $this->assertSame(
            ['a' => 2, 'b' => 3, 'c' => 4],
            ArrayHelper::map($data, fn ($v) => $v + 1)
        );

        $this->assertSame(
            ['a' => 'A', 'b' => 2, 'c' => 3],
            ArrayHelper::map($data, fn ($v, $p) => 'a' === $p ? 'A' : $v)
        );

        $recursiveData = [1 => [2 => [3 => ['test' => 0]]]];

        $this->assertSame(
            [1 => [2 => [3 => ['test' => 'MAP']]]],
            ArrayHelper::map($recursiveData, fn ($v, $p) => 'test' === $p ? 'MAP' : $v)
        );

        $this->assertSame(
            [1 => 'flat'],
            ArrayHelper::map($recursiveData, fn ($v, $p) => 1 === $p ? 'flat' : $v)
        );
    }
}
