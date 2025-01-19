<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common;

use EMS\CommonBundle\Common\Converter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    private Converter $converter;

    #[\Override]
    protected function setUp(): void
    {
        $this->converter = new Converter();
        parent::setUp();
    }

    /**
     * format: [text,text].
     *
     * @return array<array<string>>
     */
    public static function strProvider(): array
    {
        return [
            ['test', 'test'],
            ['TEST', 'test'],
            ['À', 'a'],
            ['È', 'e'],
            ['[-test\+&test]', 'testtest'],
        ];
    }

    #[DataProvider('strProvider')]
    public function testToAscii(string $str, string $expected): void
    {
        self::assertSame($expected, $this->converter->toAscii($str));
    }

    /**
     * format: [int,text].
     *
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
        self::assertSame($expected, $this->converter->formatBytes($byte));
        self::assertSame($expected2, $this->converter->formatBytes($byte, 0));
        self::assertSame($expected3, $this->converter->formatBytes($byte, 4));
    }
}
