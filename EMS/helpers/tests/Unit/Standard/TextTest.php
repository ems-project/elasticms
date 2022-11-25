<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Text;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    public function testFoobar()
    {
        self::assertEquals('foo bar', Text::superTrim("\n\r
                foo \t\t

                bar

        "));
    }
}
