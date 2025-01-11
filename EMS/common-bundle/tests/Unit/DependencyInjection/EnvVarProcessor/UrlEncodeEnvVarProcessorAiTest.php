<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\DependencyInjection\EnvVarProcessor;

use EMS\CommonBundle\DependencyInjection\EnvVarProcessor\UrlEncodeEnvVarProcessor;
use PHPUnit\Framework\TestCase;

final class UrlEncodeEnvVarProcessorAiTest extends TestCase
{
    private UrlEncodeEnvVarProcessor $processor;

    #[\Override]
    protected function setUp(): void
    {
        $this->processor = new UrlEncodeEnvVarProcessor();
    }

    public function testGetEnv(): void
    {
        $value = 'Hello World!';
        $expectedEncodedValue = 'Hello+World%21';

        $result = $this->processor->getEnv('urlencode', 'TEST_ENV_VAR', fn () => $value);

        $this->assertEquals($expectedEncodedValue, $result);
    }

    public function testGetProvidedTypes(): void
    {
        $expectedTypes = [
            'urlencode' => 'string',
        ];

        $this->assertEquals($expectedTypes, UrlEncodeEnvVarProcessor::getProvidedTypes());
    }
}
