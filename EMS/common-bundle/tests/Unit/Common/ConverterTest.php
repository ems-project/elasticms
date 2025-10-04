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
}
