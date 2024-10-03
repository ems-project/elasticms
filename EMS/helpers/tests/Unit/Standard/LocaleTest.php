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
    public function testShortLocale(?string $input, ?string $default, string $expected): void
    {
        $this->assertEquals($expected, Locale::short($input, $default));
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
            ['french', 'en', 'en'],
            [null, 'en', 'en'],
            [null, null, 'en'],
        ];
    }
}
