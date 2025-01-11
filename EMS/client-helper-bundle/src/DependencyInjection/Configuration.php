<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\DependencyInjection;

use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Property;
use EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider\AzureOAuth2Provider;
use EMS\ClientHelperBundle\Security\Sso\Saml\SamlProperty;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ems_client_helper');
        /* @var $rootNode ArrayNodeDefinition */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->variableNode('request_environments')->isRequired()->end()
                ->variableNode('locales')->isRequired()->end()
                ->booleanNode('bind_locale')->end()
                ->booleanNode('handle_exceptions')->defaultTrue()->end()
                ->scalarNode('etag_hash_algo')->end()
                ->scalarNode('asset_local_folder')->end()
                ->integerNode('search_limit')
                    ->info('search limit to get local documents')
                    ->defaultValue(1000)
                ->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('error')->defaultValue('@EMSCH/template/error{code}.html.twig')->end()
                        ->scalarNode('ems_link')->defaultValue('@EMSCH/template/emsLinks/{type}.ems_link.twig')->end()
                    ->end()
                ->end()
            ->end()
        ;

        $this->addElasticmsSection($rootNode);
        $this->addApiSection($rootNode);
        $this->addUserApiSection($rootNode);
        $this->addLocalSection($rootNode);
        $this->addSecuritySection($rootNode);

        return $treeBuilder;
    }

    private function addElasticmsSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('elasticms')
                    ->beforeNormalization()
                        ->ifArray()
                        ->then(function ($v) {
                            if (1 === (\is_countable($v) ? \count($v) : 0)) {
                                $v[\key($v)]['default'] = true;

                                return $v;
                            }

                            $default = [];

                            foreach ($v as $name => $options) {
                                if (isset($options['default']) && $options['default']) {
                                    $default[] = $name;
                                }
                            }

                            if (empty($default)) {
                                throw new \InvalidArgumentException('no default elasticms configured');
                            }

                            if (\count($default) > 1) {
                                throw new \InvalidArgumentException('there can only be 1 default elasticms');
                            }

                            return $v;
                        })
                    ->end()
                    ->prototype('array')
                        ->info('name for the ems-project')
                        ->children()
                            ->variableNode('hosts')
                                ->info('elasticsearch hosts')
                                ->isRequired()
                            ->end()
                            ->booleanNode('default')->end()
                            ->scalarNode('translation_type')
                                ->info("example: 'test_i18n'")
                                ->defaultValue(null)
                            ->end()
                            ->scalarNode('route_type')
                                ->defaultValue(null)
                            ->end()
                            ->scalarNode('asset_config_type')
                                ->defaultValue(null)
                            ->end()
                            ->variableNode('templates')
                                ->example('{"template": {"name": "key","code": "body"}}')
                            ->end()
                            ->variableNode('api')
                                ->info('api for content exposing')
                                ->example('{"enable": true, "name": "api"}')
                            ->end()
                            ->variableNode('search_config')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addApiSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('api')
                    ->prototype('array')
                        ->info('name for the ems-project')
                        ->children()
                            ->scalarNode('url')
                                ->info('url of the elasticms without /api')
                                ->isRequired()
                            ->end()
                            ->scalarNode('key')
                                ->info('api key')
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addUserApiSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('user_api')
                    ->canBeEnabled()
                        ->children()
                            ->scalarNode('url')
                                ->info('url of the elasticms without /user_api')
                                ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addLocalSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('local')
                    ->canBeEnabled()
                        ->children()
                            ->scalarNode('path')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addSecuritySection(ArrayNodeDefinition $rootNode): void
    {
        $security = $rootNode->children()->arrayNode('security')->addDefaultsIfNotSet()->children();
        $security->scalarNode('route_login')->defaultValue('emsch_login')->end();

        $sso = $security->arrayNode('sso')->children();

        $oAuth2 = $sso->arrayNode('oauth2')->canBeEnabled()->children();
        foreach (OAuth2Property::cases() as $oAuth2Property) {
            match ($oAuth2Property) {
                OAuth2Property::PROVIDER => $oAuth2->scalarNode($oAuth2Property->value)->defaultValue('keycloak'),
                OAuth2Property::SCOPES => $oAuth2
                    ->variableNode($oAuth2Property->value)
                    ->defaultValue(AzureOAuth2Provider::DEFAULT_SCOPES),
                default => $oAuth2->scalarNode($oAuth2Property->value),
            };
        }

        $saml = $sso->arrayNode('saml')->canBeEnabled()->children();
        foreach (SamlProperty::cases() as $samlProperty) {
            match ($samlProperty) {
                SamlProperty::SECURITY => $saml->variableNode($samlProperty->value)->end(),
                default => $saml->scalarNode($samlProperty->value)->end(),
            };
        }
    }
}
