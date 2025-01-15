<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\DependencyInjection;

use EMS\CommonBundle\DependencyInjection\EMSCommonExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EMSCommonExtensionAiTest extends TestCase
{
    private EMSCommonExtension $extension;
    private ContainerBuilder $container;

    #[\Override]
    protected function setUp(): void
    {
        $this->extension = new EMSCommonExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoadWithFullConfig(): void
    {
        $configs = [
            [
                'backend_url' => 'http://example.com',
                'backend_api_key' => 'test_key',
                'elasticsearch_proxy_api' => true,
                'elasticsearch_hosts' => ['http://localhost:9200'],
                'elasticsearch_connection_pool' => 'static',
                'storages' => ['local'],
                'store_data_services' => ['file_system'],
                'log_level' => 200,
                'excluded_content_types' => ['test'],
                'core_api' => [
                    'verify' => true,
                    'timeout' => 30,
                ],
                'cache' => [
                    'type' => 'file_system',
                    'prefix' => 'ems_cache',
                    'redis' => [
                        'host' => 'localhost',
                        'port' => '6379',
                    ],
                ],
                'webalize' => [
                    'removable_regex' => '/[^a-zA-Z0-9\_\|\ \-\.]/',
                    'dashable_regex' => '/[\/\|\ ]+/',
                ],
                'metric' => [
                    'enabled' => true,
                    'host' => 'localhost',
                    'port' => '9100',
                ],
            ],
        ];

        $this->extension->load($configs, $this->container);

        $this->assertEquals('http://example.com', $this->container->getParameter('ems_common.backend_url'));
        $this->assertEquals('test_key', $this->container->getParameter('ems_common.backend_api_key'));
        $this->assertTrue($this->container->getParameter('ems_common.elasticsearch_proxy_api'));
        $this->assertEquals(['http://localhost:9200'], $this->container->getParameter('ems_common.elasticsearch_hosts'));
        $this->assertEquals('static', $this->container->getParameter('ems_common.elasticsearch_connection_pool'));
        $this->assertEquals(['local'], $this->container->getParameter('ems_common.storages'));
        $this->assertEquals(['file_system'], $this->container->getParameter('ems_common.store_data_services'));
        $this->assertEquals(200, $this->container->getParameter('ems_common.log_level'));
        $this->assertEquals(['test'], $this->container->getParameter('ems_common.excluded_content_types'));
        $this->assertEquals('/[^a-zA-Z0-9\_\|\ \-\.]/', $this->container->getParameter('ems_common.webalize.removable_regex'));
        $this->assertEquals('/[\/\|\ ]+/', $this->container->getParameter('ems_common.webalize.dashable_regex'));
        $this->assertTrue($this->container->getParameter('ems.metric.enabled'));
        $this->assertEquals('localhost', $this->container->getParameter('ems.metric.host'));
        $this->assertEquals('9100', $this->container->getParameter('ems.metric.port'));
    }
}
