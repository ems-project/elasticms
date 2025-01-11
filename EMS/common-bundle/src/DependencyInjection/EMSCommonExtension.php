<?php

declare(strict_types=1);

namespace EMS\CommonBundle\DependencyInjection;

use EMS\CommonBundle\Common\CoreApi\CoreApi;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class EMSCommonExtension extends Extension
{
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('contracts.xml');
        $loader->load('log.xml');
        $loader->load('services.xml');
        $loader->load('commands.xml');
        $loader->load('twig.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader->load('storage.xml');
        $loader->load('store_data.xml');

        if ($config['profiler']) {
            $loader->load('profiler.xml');
        }

        $container->setParameter('ems_common.hash_algo', $config['hash_algo']);
        $container->setParameter('ems_common.backend_url', $config['backend_url']);
        $container->setParameter('ems_common.backend_api_key', $config['backend_api_key']);
        $container->setParameter('ems_common.elasticsearch_proxy_api', $config['elasticsearch_proxy_api']);
        $container->setParameter('ems_common.elasticsearch_hosts', $config['elasticsearch_hosts']);
        $container->setParameter('ems_common.elasticsearch_connection_pool', $config['elasticsearch_connection_pool']);
        $container->setParameter('ems_common.storages', $config['storages']);
        $container->setParameter('ems_common.store_data_services', $config['store_data_services']);
        $container->setParameter('ems_common.log_level', $config['log_level']);
        $container->setParameter('ems_common.excluded_content_types', $config['excluded_content_types']);
        $container->setParameter('ems_common.slug_symbol_map', $config['slug_symbol_map']);
        $container->setParameter('ems_common.request.trusted_ips', $config['request']['trusted_ips']);

        $container->setParameter('ems_common.cache_config', $config['cache']);

        $container->setParameter('ems_common.webalize.removable_regex', $config['webalize']['removable_regex']);
        $container->setParameter('ems_common.webalize.dashable_regex', $config['webalize']['dashable_regex']);

        $this->defineCoreApi($container, $config);

        $metricsEnabled = $config['metric']['enabled'] ?? false;
        $container->setParameter('ems.metric.enabled', $metricsEnabled);
        if ($metricsEnabled) {
            $container->setParameter('ems.metric.host', $config['metric']['host'] ?? null);
            $container->setParameter('ems.metric.port', $config['metric']['port'] ?? null);
            $loader->load('metric.xml');
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function defineCoreApi(ContainerBuilder $container, array $config): void
    {
        $container->getDefinition('ems_common.core_api.factory')->setArgument(2, $config['core_api']);

        if (!isset($config['backend_url'])) {
            return;
        }

        $definition = new Definition(CoreApi::class);
        $definition
            ->setFactory([new Reference('ems_common.core_api.factory'), 'create'])
            ->addArgument($config['backend_url']);

        if (isset($config['backend_api_key'])) {
            $definition->addMethodCall('setToken', [$config['backend_api_key']]);
        }

        $container->setDefinition('ems_common.core_api', $definition);
        $container->setAlias(CoreApiInterface::class, 'ems_common.core_api');
    }
}
