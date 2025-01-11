<?php

declare(strict_types=1);

namespace EMS\CommonBundle\DependencyInjection;

use Monolog\Logger;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private const array ELASTICSEARCH_DEFAULT_HOSTS = ['http://localhost:9200'];
    private const int LOG_LEVEL = Logger::NOTICE;
    final public const string WEBALIZE_REMOVABLE_REGEX = "/([^a-zA-Z0-9_| \-.'\/])|(\.$)/";
    final public const string WEBALIZE_DASHABLE_REGEX = "/[\/| ']+/";

    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ems_common');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->variableNode('storages')->defaultValue([])->end()
                ->variableNode('store_data_services')->defaultValue([])->end()
                ->booleanNode('profiler')->defaultFalse()->end()
                ->scalarNode('hash_algo')->defaultValue('sha1')->end()
                ->scalarNode('backend_url')->defaultValue(null)->end()
                ->scalarNode('backend_api_key')->defaultValue(null)->end()
                ->scalarNode('elasticsearch_proxy_api')->defaultValue(false)->end()
                ->scalarNode('elasticsearch_connection_pool')->defaultValue(null)->end()
                ->variableNode('elasticsearch_hosts')->defaultValue(self::ELASTICSEARCH_DEFAULT_HOSTS)->end()
                ->integerNode('log_level')->defaultValue(self::LOG_LEVEL)->end()
                ->variableNode('excluded_content_types')->defaultValue([])->end()
                ->variableNode('slug_symbol_map')->defaultValue(null)->end()
            ->end()
        ;

        $this->addCacheSection($rootNode);
        $this->addCoreApiSection($rootNode);
        $this->addMetricSection($rootNode);
        $this->addWebalizeSection($rootNode);
        $this->addRequestSection($rootNode);

        return $treeBuilder;
    }

    private function addCacheSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('type')->defaultValue('file_system')->end()
                        ->scalarNode('prefix')->defaultValue('ems_cache')->end()
                        ->arrayNode('redis')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('host')->cannotBeEmpty()->end()
                                ->scalarNode('port')->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addCoreApiSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('core_api')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->variableNode('headers')->defaultValue([])->end()
                            ->scalarNode('max_connections')->defaultValue(6)->end()
                            ->scalarNode('timeout')->defaultValue(30)->end()
                            ->scalarNode('verify')->defaultValue(true)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addMetricSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('metric')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('enabled')->cannotBeEmpty()->end()
                        ->scalarNode('host')->end()
                        ->scalarNode('port')->defaultNull()->end()
                ->end()
            ->end()
        ;
    }

    private function addWebalizeSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('webalize')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('removable_regex')->defaultValue(self::WEBALIZE_REMOVABLE_REGEX)->setDeprecated('elasticms/common-bundle', '6.0.0')->end()
                        ->scalarNode('dashable_regex')->defaultValue(self::WEBALIZE_DASHABLE_REGEX)->setDeprecated('elasticms/common-bundle', '6.0.0')->end()
                ->end()
            ->end()
        ;
    }

    private function addRequestSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('request')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->variableNode('trusted_ips')->defaultValue([])->end()
                ->end()
            ->end()
        ;
    }
}
