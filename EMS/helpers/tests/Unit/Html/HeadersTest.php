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
            self::assertTrue(Headers::validate($name));
        }
    }

    public function testNormalizeHeaderNames(): void
    {
        self::assertSame('Content-Length', Headers::normalize('content-length'));
        self::assertSame('Content-Length', Headers::normalize('CONTENT-LENGTH'));
        self::assertSame('X-Custom-Header', Headers::normalize('x-custom-header'));
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
            self::assertFalse(Headers::validate($name));
        }
    }
}
