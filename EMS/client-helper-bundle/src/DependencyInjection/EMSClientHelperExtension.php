<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\DependencyInjection;

use EMS\ClientHelperBundle\Helper\Api\Client as ApiClient;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\ClientHelperBundle\Helper\Templating\TemplateLoader;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class EMSClientHelperExtension extends Extension
{
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('builders.xml');
        $loader->load('services.xml');
        $loader->load('routing.xml');
        $loader->load('search.xml');
        $loader->load('security.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('emsch.locales', $config['locales']);
        $container->setParameter('emsch.bind_locale', $config['bind_locale'] ?? true);
        $container->setParameter('emsch.handle_exceptions', $config['handle_exceptions'] ?? true);
        $container->setParameter('emsch.etag_hash_algo', $config['etag_hash_algo'] ?? 'sha1');
        $container->setParameter('emsch.asset_local_folder', $config['asset_local_folder'] ?? null);
        $container->setParameter('emsch.request_environments', $config['request_environments']);
        $container->setParameter('emsch.search_limit', $config['search_limit']);
        $container->setParameter('emsch.security.sso.oauth2', $config['security']['sso']['oauth2'] ?? []);
        $container->setParameter('emsch.security.sso.saml', $config['security']['sso']['saml'] ?? []);
        $container->setParameter('emsch.security.route_login', $config['security']['route_login']);

        $templates = $config['templates'];
        $container->getDefinition('emsch.helper_exception')->replaceArgument(5, $templates['error']);
        $container->getDefinition('emsch.routing.url.transformer')->replaceArgument(5, $templates['ems_link']);

        $this->processElasticms($container, $loader, $config['elasticms']);
        $this->processApi($container, $config['api']);

        if ($config['local']['enabled']) {
            $container->setParameter('emsch.local.path', $config['local']['path']);
            $loader->load('local.xml');
        }

        if ($config['user_api']['enabled']) {
            $container->setParameter('emsch.user_api.url', $config['user_api']['url']);
            $loader->load('user_api.xml');
        }

        $loader->load('api.xml');
    }

    /**
     * @param array<string, mixed> $config
     */
    private function processElasticms(ContainerBuilder $container, XmlFileLoader $loader, array $config): void
    {
        foreach ($config as $name => $options) {
            $this->defineClientRequest($container, $loader, $name, $options);

            if (isset($options['templates'])) {
                $this->defineTwigLoader($container, $name);
            }
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function processApi(ContainerBuilder $container, array $config): void
    {
        foreach ($config as $name => $options) {
            $definition = new Definition(ApiClient::class);
            $definition->setArgument(0, $name);
            $definition->setArgument(1, $options['url']);
            $definition->setArgument(2, $options['key']);
            $definition->setArgument(3, new Reference('ems_common.core_api'));
            $definition->addTag('emsch.api_client');

            $container->setDefinition(\sprintf('emsch.api_client.%s', $name), $definition);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    private function defineClientRequest(ContainerBuilder $container, XmlFileLoader $loader, string $name, array $options): void
    {
        $definition = new Definition(ClientRequest::class);
        $definition->setArguments([
            new Reference('ems_common.service.elastica'),
            new Reference('emsch.helper_environment'),
            new Reference('emsch.helper_cache'),
            new Reference('emsch.helper_content_type'),
            new Reference('logger'),
            new Reference(CacheItemPoolInterface::class),
            $name,
            $options,
        ]);
        $definition->addTag('emsch.client_request');

        if (isset($options['api'])) {
            $definition->addTag('emsch.client_request.api');
        }

        $container->setDefinition(\sprintf('emsch.client_request.%s', $name), $definition);
    }

    private function defineTwigLoader(ContainerBuilder $container, string $name): void
    {
        $loader = new Definition(TemplateLoader::class);
        $loader->setArguments([
            new Reference('emsch.helper_environment'),
            new Reference('emsch.helper.templating.builder'),
        ]);
        $loader->addTag('twig.loader', ['alias' => $name, 'priority' => 1]);

        $container->setDefinition(\sprintf('emsch.twig.loader.%s', $name), $loader);
    }
}
