<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\Common;

use EMS\CommonBundle\Common\Converter;
use PHPUnit\Framework\TestCase;

class ConverterAiTest extends TestCase
{
    public function testToAscii(): void
    {
        $this->assertEquals('hello-world', Converter::toAscii('Hello World'));
        $this->assertEquals('emsasset', Converter::toAscii('ems://asset'));
        $this->assertEquals('a-e-i-o-u', Converter::toAscii('À É Í Ó Ú'));
    }

    public function testStringify(): void
    {
        $this->assertEquals('hello', Converter::stringify('hello'));
        $this->assertEquals('123', Converter::stringify(123));
        $this->assertEquals('{"key":"value"}', Converter::stringify(['key' => 'value']));
    }
}
