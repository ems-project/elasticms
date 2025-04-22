<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Number;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NumberTest extends TestCase
{
    /**
     * @return array<array<int|string>>
     */
    public static function byteProvider(): array
    {
        return [
            [243, '243 B', '243 B', '243 B'],
            [2496, '2.44 KB', '2 KB', '2.4375 KB'],
            [24_962_496, '23.81 MB', '24 MB', '23.8061 MB'],
            [249_624_962_496, '232.48 GB', '232 GB', '232.4814 GB'],
            [2_496_249_624_962_496, '2270.33 TB', '2270 TB', '2270.3258 TB'],
        ];
    }

    #[DataProvider('byteProvider')]
    public function testBytes(int $byte, string $expected, string $expected2, string $expected3): void
    {
        self::assertSame($expected, Number::formatBytes($byte));
        self::assertSame($expected2, Number::formatBytes($byte, 0));
        self::assertSame($expected3, Number::formatBytes($byte, 4));
    }
}
