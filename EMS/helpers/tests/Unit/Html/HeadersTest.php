<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Html;

use EMS\Helpers\Html\Headers;
use PHPUnit\Framework\TestCase;

class HeadersTest extends TestCase
{
    public function testValidHeaderNames(): void
    {
        foreach ([
            'Content-Type',
            'X-Custom_Header.1',
            "!#$%&'*+-.^_`|~",
        ] as $name) {
            self::assertTrue(Headers::validateName($name));
        }
    }

    public function testNormalizeHeaderNames(): void
    {
        self::assertSame('Content-Length', Headers::normalizeName('content-length'));
        self::assertSame('Content-Length', Headers::normalizeName('CONTENT-LENGTH'));
        self::assertSame('X-Custom-Header', Headers::normalizeName('x-custom-header'));
    }

    public function testInvalidHeaderNames(): void
    {
        foreach ([
            '',
            'Content Type',
            'Content:Type',
            "Content\rType",
            "Content\nType",
            "Content\r\nInjected: value",
            "é",
        ] as $name) {
            self::assertFalse(Headers::validateName($name));
        }
    }
}
