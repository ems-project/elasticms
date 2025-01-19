<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Elasticsearch;

use EMS\CommonBundle\Elasticsearch\QueryStringEscaper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QueryStringEscaperTest extends TestCase
{
    #[DataProvider('provider')]
    public function testEscape(string $input, string $output): void
    {
        $this->assertSame($output, QueryStringEscaper::escape($input));
    }

    public static function provider(): array
    {
        return [
            ['example \ / ', 'example \\\ \\/ '],
            ['test & = test ! ', 'test \\& \\= test \\! '],
            ['<code> ~ + ? test', '\\<code\\> \\~ \\+ \\? test'],
            ['e-example \ 01/06', 'e\\-example \\\ 01\\/06'],
            ['e-example \ ', 'e\\-example \\\ '],
        ];
    }
}
