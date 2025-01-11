<?php

namespace EMS\FormBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    final public const string LOAD_FROM_JSON = 'load_from_json';
    final public const string SUBMISSION_FIELD = 'submission_field';
    final public const string THEME_FIELD = 'theme_field';
    final public const string FORM_TEMPLATE_FIELD = 'form_template_field';
    final public const string FORM_FIELD = 'form_field';
    final public const string FORM_SUBFORM_FIELD = 'form_subform_field';
    final public const string TYPE_FORM_CHOICE = 'type_form_choice';
    final public const string TYPE_FORM_SUBFORM = 'type_form_subform';
    final public const string TYPE_FORM_MARKUP = 'type_form_markup';
    final public const string TYPE_FORM_FIELD = 'type_form_field';
    final public const string TYPE_FORM_VALIDATION = 'type_form_validation';
    final public const string TYPE = 'type';
    final public const string HASHCASH_DIFFICULTY = 'hashcash_difficulty';
    final public const string ENDPOINTS = 'endpoints';
    final public const string DOMAIN_FIELD = 'domain';
    final public const string CACHEABLE = 'cacheable';
    final public const string NAME_FIELD = 'name';

    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ems_form');
        /* @var $rootNode ArrayNodeDefinition */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->variableNode(self::HASHCASH_DIFFICULTY)->defaultValue(16384)->end()
                ->variableNode(self::ENDPOINTS)
                    ->defaultValue([])
                    ->example('[{"field_name":"send_confirmation","http_request":{"url":"https://api.example.test/v1/send/sms","headers":{"Content-Type":"application/json"},"body":"{\"To\": \"%value%\", \"Message\": \"%verification_code%\"}"}}]')
                ->end()
                ->arrayNode('instance')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode(self::TYPE)->defaultValue('form_instance')->end()
                        ->scalarNode(self::TYPE_FORM_FIELD)->defaultValue('form_structure_field')->end()
                        ->scalarNode(self::TYPE_FORM_MARKUP)->defaultValue('form_structure_markup')->end()
                        ->scalarNode(self::TYPE_FORM_SUBFORM)->defaultValue('form_structure')->end()
                        ->scalarNode(self::TYPE_FORM_CHOICE)->defaultValue('form_choice')->end()
                        ->scalarNode(self::TYPE_FORM_VALIDATION)->defaultValue('form_validation')->end()
                        ->scalarNode(self::FORM_FIELD)->defaultValue('form')->end()
                        ->scalarNode(self::FORM_SUBFORM_FIELD)->defaultValue('sub_form')->end()
                        ->scalarNode(self::FORM_TEMPLATE_FIELD)->defaultValue('form_template')->end()
                        ->scalarNode(self::THEME_FIELD)->defaultValue('theme_template')->end()
                        ->scalarNode(self::SUBMISSION_FIELD)->defaultValue('submissions')->end()
                        ->scalarNode(self::DOMAIN_FIELD)->defaultValue('domain')->end()
                        ->scalarNode(self::NAME_FIELD)->defaultValue('name')->end()
                        ->scalarNode(self::LOAD_FROM_JSON)->defaultValue(false)->end()
                        ->scalarNode(self::CACHEABLE)->defaultValue(true)->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
