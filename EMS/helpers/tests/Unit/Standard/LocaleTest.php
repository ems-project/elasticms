<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Locale;
use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    /**
     * @dataProvider provideLocaleInput
     */
    public function testLanguage(?string $input, ?string $default, string $expected): void
    {
        $this->assertEquals($expected, Locale::getLanguage($input, $default));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid locale passed "toto"');

        Locale::getLanguage('toto', 'tada');
        Locale::getLanguage(null, 'toto');
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    public function provideLocaleInput(): array
    {
        return [
            ['fr', 'en', 'fr'],
            ['fr', null, 'fr'],
            ['FR', 'en', 'fr'],
            ['nl_BE', null, 'nl'],
            ['FR_BE', null, 'fr'],
            [null, 'en', 'en'],
            [null, null, 'en'],
        ];
    }
}
