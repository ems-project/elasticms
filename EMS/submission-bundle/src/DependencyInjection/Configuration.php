<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ems_submission');
        /* @var $rootNode ArrayNodeDefinition */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('default_timeout')->defaultValue('10')->end()
                ->variableNode('connections')
                    ->example('[{"connection": "conn-id", "user": "your-username": "password": "your-password"}]')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
