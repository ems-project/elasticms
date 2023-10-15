<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Standard;

use EMS\CommonBundle\Common\Standard\Json;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

class JsonAiTest extends TestCase
{
    use ExpectDeprecationTrait;

    private const TEST_JSON_STRING = '{"key": "value"}';
    private const TEST_JSON_ARRAY = ['key' => 'value'];

    /**
     * @group legacy
     */
    public function testEncodeTriggersDeprecation(): void
    {
        $this->expectDeprecation('The function %s::encode has been deprecated, use %s::encode instead');
        Json::encode(self::TEST_JSON_ARRAY);
    }

    /**
     * @group legacy
     */
    public function testEscapeTriggersDeprecation(): void
    {
        $this->expectDeprecation('The function %s::escape has been deprecated, use %s::escape instead');
        Json::escape(self::TEST_JSON_STRING);
    }

    /**
     * @group legacy
     */
    public function testDecodeTriggersDeprecation(): void
    {
        $this->expectDeprecation('The function %s::decode has been deprecated, use %s::decode instead');
        Json::decode(self::TEST_JSON_STRING);
    }

    /**
     * @group legacy
     */
    public function testDecodeFileTriggersDeprecation(): void
    {
        $this->expectDeprecation('The function %s::decodeFile has been deprecated, use %s::decodeFile instead');
        $tempFile = \tempnam(\sys_get_temp_dir(), 'json_test');
        \file_put_contents($tempFile, self::TEST_JSON_STRING);
        Json::decodeFile($tempFile);
        \unlink($tempFile);
    }

    /**
     * @group legacy
     */
    public function testIsJsonTriggersDeprecation(): void
    {
        $this->expectDeprecation('The function %s::isJson has been deprecated, use %s::isJson instead');
        Json::isJson(self::TEST_JSON_STRING);
    }
}
