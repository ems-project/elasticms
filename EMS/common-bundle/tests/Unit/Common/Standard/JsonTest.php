<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Standard;

use EMS\Helpers\Standard\Json;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testIsJson(): void
    {
        $this->assertTrue(Json::isJson('null'));
        $this->assertTrue(Json::isJson('54'));
        $this->assertTrue(Json::isJson('"Foobar"'));
        $this->assertTrue(Json::isJson('{"foo":"bar"}'));
        $this->assertTrue(Json::isJson('[{"foo":"bar"},{"foo":"bar"}]'));
    }

    public function testIsNotJson(): void
    {
        $this->assertFalse(Json::isJson('FOOBAR'));
        $this->assertFalse(Json::isJson('Foobar'));
        $this->assertFalse(Json::isJson('{"foo":"bar"'));
        $this->assertFalse(Json::isJson('[{"foo":"bar"}{"toto":"tata"}]'));
    }
}
