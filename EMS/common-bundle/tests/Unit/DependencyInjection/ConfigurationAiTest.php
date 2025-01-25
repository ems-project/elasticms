<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\DependencyInjection;

use EMS\CommonBundle\DependencyInjection\Configuration;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationAiTest extends TestCase
{
    private Configuration $configuration;

    #[\Override]
    protected function setUp(): void
    {
        $this->configuration = new Configuration();
    }

    public function testDefaultConfig(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration($this->configuration, []);

        $expected = [
            'storages' => [],
            'store_data_services' => [],
            'profiler' => false,
            'hash_algo' => 'sha1',
            'backend_url' => null,
            'backend_api_key' => null,
            'elasticsearch_proxy_api' => false,
            'elasticsearch_connection_pool' => null,
            'elasticsearch_hosts' => ['http://localhost:9200'],
            'log_level' => Logger::NOTICE,
            'excluded_content_types' => [],
            'core_api' => [
                'headers' => [],
                'verify' => true,
                'timeout' => 30,
            ],
            'cache' => [
                'type' => 'file_system',
                'prefix' => 'ems_cache',
                'redis' => [
                ],
            ],
            'metric' => [
                'port' => null,
            ],
            'webalize' => [
                'removable_regex' => Configuration::WEBALIZE_REMOVABLE_REGEX,
                'dashable_regex' => Configuration::WEBALIZE_DASHABLE_REGEX,
            ],
            'slug_symbol_map' => null,
            'request' => [
                'trusted_ips' => [],
            ],
        ];

        $this->assertEquals($expected, $config);
    }
}
