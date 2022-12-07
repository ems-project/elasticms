<?php

declare(strict_types=1);

namespace EMS\CommonBundle\DependencyInjection;

use Monolog\Logger;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private const ELASTICSEARCH_DEFAULT_HOSTS = ['http://localhost:9200'];
    private const LOG_LEVEL = Logger::NOTICE;
    final public const WEBALIZE_REMOVABLE_REGEX = "/([^a-zA-Z\_\|\ \-\.])|(\.$)/";
    final public const WEBALIZE_DASHABLE_REGEX = "/[\/\|\ ]+/";

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ems_common');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->variableNode('storages')->defaultValue([])->end()
                ->booleanNode('profiler')->defaultFalse()->end()
                ->scalarNode('hash_algo')->defaultValue('sha1')->end()
                ->scalarNode('backend_url')->defaultValue(null)->end()
                ->scalarNode('backend_api_key')->defaultValue(null)->end()
                ->scalarNode('backend_api_insecure')->defaultValue(false)->end()
                ->variableNode('elasticsearch_hosts')->defaultValue(self::ELASTICSEARCH_DEFAULT_HOSTS)->end()
                ->integerNode('log_level')->defaultValue(self::LOG_LEVEL)->end()
            ->end()
        ;

        $this->addCacheSection($rootNode);
        $this->addMetricSection($rootNode);
        $this->addWebalizeSection($rootNode);

        return $treeBuilder;
    }

    private function addCacheSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('type')->defaultValue('filesystem')->end()
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
                        ->scalarNode('removable_regex')->defaultValue(self::WEBALIZE_REMOVABLE_REGEX)->end()
                        ->scalarNode('dashable_regex')->defaultValue(self::WEBALIZE_DASHABLE_REGEX)->end()
                ->end()
            ->end()
        ;
    }
}
