<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\ClientHelperBundle\Command\HealthCheckCommand;
use EMS\ClientHelperBundle\Command\HttpCache\InvalidateCommand;
use EMS\ClientHelperBundle\Contracts\Elasticsearch\ClientRequestManagerInterface;
use EMS\ClientHelperBundle\Contracts\Environment\EnvironmentHelperInterface;
use EMS\ClientHelperBundle\Controller\CoreBridgeController;
use EMS\ClientHelperBundle\Controller\EmbedController;
use EMS\ClientHelperBundle\Controller\HttpCacheController;
use EMS\ClientHelperBundle\Controller\SearchController;
use EMS\ClientHelperBundle\EventListener\CacheListener;
use EMS\ClientHelperBundle\EventListener\KernelListener;
use EMS\ClientHelperBundle\EventListener\SecurityListener;
use EMS\ClientHelperBundle\Helper\Asset\AssetVersionStrategy;
use EMS\ClientHelperBundle\Helper\Asset\ClientHelperAssetVersionStrategy;
use EMS\ClientHelperBundle\Helper\Cache\CacheHelper;
use EMS\ClientHelperBundle\Helper\ContentType\ContentTypeHelper;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\ClientHelperBundle\Helper\Environment\EnvironmentFactory;
use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use EMS\ClientHelperBundle\Helper\Form\Extension\EmschFormViewExtension;
use EMS\ClientHelperBundle\Helper\Hashcash\HashcashHelper;
use EMS\ClientHelperBundle\Helper\Request\EmschRequestResolver;
use EMS\ClientHelperBundle\Helper\Request\ExceptionHelper;
use EMS\ClientHelperBundle\Helper\Request\LocaleHelper;
use EMS\ClientHelperBundle\Helper\Translation\Translator;
use EMS\ClientHelperBundle\Helper\Webhook\WebhookHelper;
use EMS\ClientHelperBundle\Twig\AdminMenuExtension;
use EMS\ClientHelperBundle\Twig\AssetExtension;
use EMS\ClientHelperBundle\Twig\HelperExtension;
use EMS\ClientHelperBundle\Twig\InlineEditExtension;
use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;
use EMS\CommonBundle\Contracts\Elasticsearch\QueryLoggerInterface;
use Psr\Cache\CacheItemPoolInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('env(EMSCH_ENV)', '');

    $services->defaults()
        ->private();

    $services->alias(ClientRequestManagerInterface::class, 'emsch.manager.client_request');

    $services->set('emsch.manager.client_request', ClientRequestManager::class)
        ->args([
            tagged_iterator('emsch.client_request'),
            service('logger'),
        ])
        ->tag('monolog.logger', ['channel' => 'emsch_manager']);

    $services->set('emsch.helper_content_type', ContentTypeHelper::class);

    $services->alias(EnvironmentHelperInterface::class, 'emsch.helper_environment');

    $services->set('emsch.helper_environment', EnvironmentHelper::class)
        ->args([
            service('emsch.helper.environment_factory'),
            service('request_stack'),
            '%env(string:EMSCH_ENV)%',
            '%emsch.request_environments%',
        ]);

    $services->set('emsch.helper.environment_factory', EnvironmentFactory::class)
        ->call('setLocalEnvironmentFactory', [service('emsch.helper.local_environment_factory')->nullOnInvalid()]);

    $services->set('emsch.helper.request.emsch_request_resolver', EmschRequestResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 100]);

    $services->set('emsch.helper_locale', LocaleHelper::class)
        ->args(['%emsch.locales%']);

    $services->set('emsch.helper_exception', ExceptionHelper::class)
        ->args([
            service('twig'),
            service('emsch.manager.client_request'),
            service('request_stack'),
            '%emsch.handle_exceptions%',
            '%kernel.debug%',
            '',
        ]);

    $services->set('emsch.helper_cache', CacheHelper::class)
        ->args([
            service(CacheItemPoolInterface::class),
            service('logger'),
            '%emsch.etag_hash_algo%',
        ]);

    $services->set('emsch.helper.translator', Translator::class)
        ->args([
            service('emsch.helper_environment'),
            service('emsch.helper.translation.builder'),
            service('translator.default'),
        ])
        ->tag('kernel.cache_warmer');

    $services->set('emsch.helper_hashcash', HashcashHelper::class)
        ->args([service('security.csrf.token_manager')]);

    $services->set('emsch.helper_webhook', WebhookHelper::class)
        ->args([
            service('request_stack'),
            service('ems.common.cache'),
        ]);

    $services->set('emsch.form.extension.view', EmschFormViewExtension::class)
        ->tag('form.type_extension', ['priority' => 1]);

    $services->set('emsch.event_listener.security', SecurityListener::class)
        ->args([
            service('security.authorization_checker'),
            service('security.token_storage'),
            service('emsch.security.sso.oauth2'),
            service('ems_common.core_api'),
            '%emsch.security.route_login%',
            '%emsch.security.firewall%',
        ])
        ->tag('kernel.event_subscriber');

    $services->set('emsch.kernel_listener', KernelListener::class)
        ->args([
            service('emsch.helper_environment'),
            service('emsch.helper.translator'),
            service('emsch.helper_locale'),
            service('emsch.helper_exception'),
            '%emsch.bind_locale%',
        ])
        ->tag('kernel.event_subscriber');

    $services->set('emsch.event_listener.cache_listener', CacheListener::class)
        ->args([
            service('emsch.helper_cache'),
            service('emsch.controller.cache'),
            service('kernel'),
            service('logger'),
            service(QueryLoggerInterface::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set('emsch.twig_extension.admin_menu', AdminMenuExtension::class)
        ->args([service('emsch.helper_environment')])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('emsch.twig_extension.asset', AssetExtension::class)
        ->args([
            service('ems_common.storage.manager'),
            service('ems.twig_extension.asset'),
            service('ems.vite'),
            '%kernel.project_dir%',
            '%emsch.asset_local_folder%',
        ])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('emsch.twig_extension.helper', HelperExtension::class)
        ->args([
            service('emsch.manager.client_request'),
            service('request_stack'),
            service('logger'),
            service('ems_common.service.elastica'),
            service('emsch.search.manager'),
            service('emsch.helper_webhook'),
        ])
        ->tag('monolog.logger', ['channel' => 'emsch_request'])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('emsch.twig_extension.inline_edit', InlineEditExtension::class)
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('emsch.asset.version_strategy', AssetVersionStrategy::class)
        ->args([service('emsch.twig_extension.asset')]);
    $services->set('emsch.asset.client_helper_version_strategy', ClientHelperAssetVersionStrategy::class)
        ->args([
            service('file_locator'),
            service('ems.vite'),
        ]);

    $services->set(CoreBridgeController::class)
        ->public()
        ->args([
            service(CoreBridgeInterface::class),
            service('router'),
        ]);

    $services->set('emsch.controller.search', SearchController::class)
        ->public()
        ->args([
            service('emsch.search.manager'),
            service('emsch.routing.handler'),
            service('emsch.helper_cache'),
        ]);

    $services->set('emsch.controller.embed', EmbedController::class)
        ->public()
        ->args([
            service('emsch.manager.client_request'),
            service('emsch.helper_cache'),
            service('twig'),
        ])
        ->call('setContainer')
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber');

    $services->set('emsch.controller.http_cache', HttpCacheController::class)
        ->public()
        ->args([
            service('ems_common.service.http_cache_manager'),
            service('emsch.helper_webhook'),
        ])
        ->call('setContainer')
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber');

    $services->alias(EmbedController::class, 'emsch.controller.embed')
        ->public();

    $services->set('emsch.command.health_check', HealthCheckCommand::class)
        ->args([
            service('emsch.helper_environment'),
            service('ems_common.service.elastica'),
            service('ems_common.storage.manager')->nullOnInvalid(),
        ])
        ->tag('console.command');

    $services->set('emsch.command.http_cache.invalidate', InvalidateCommand::class)
        ->args([
            service('ems.common.cache'),
            service('emsch.manager.client_request'),
            service('ems_common.service.http_cache_manager'),
        ])
        ->tag('console.command');
};
