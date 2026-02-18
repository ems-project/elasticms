<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\ClientHelperBundle\Contracts\Request\HandlerInterface;
use EMS\ClientHelperBundle\Controller\CacheController;
use EMS\ClientHelperBundle\Controller\ElasticsearchController;
use EMS\ClientHelperBundle\Controller\FormController;
use EMS\ClientHelperBundle\Controller\InlineEditController;
use EMS\ClientHelperBundle\Controller\PdfController;
use EMS\ClientHelperBundle\Controller\RouterController;
use EMS\ClientHelperBundle\Controller\SpreadsheetController;
use EMS\ClientHelperBundle\Helper\Request\Handler;
use EMS\ClientHelperBundle\Helper\Routing\RouteLoader;
use EMS\ClientHelperBundle\Helper\Routing\Router;
use EMS\ClientHelperBundle\Helper\Routing\Url\Generator;
use EMS\ClientHelperBundle\Helper\Routing\Url\Transformer;
use EMS\ClientHelperBundle\Twig\RoutingExtension;
use EMS\CommonBundle\Contracts\Twig\TemplateFactoryInterface;
use EMS\CommonBundle\Elasticsearch\Client;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Symfony\Component\Routing\RouterInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->set('emsch.routing.route_loader', RouteLoader::class)
        ->args([service('emsch.security.sso')->nullOnInvalid()])
        ->tag('routing.route_loader');

    $services->set('emsch.routing.chain_router', ChainRouter::class)
        ->call('add', [service('router.default')])
        ->call('setContext', [service('router.request_context')]);

    $services->set('emsch.helper.router', Router::class)
        ->args([
            service('emsch.helper_environment'),
            service('emsch.helper.routing.builder'),
        ])
        ->tag('emsch.router', ['priority' => 100]);

    $services->alias(HandlerInterface::class, 'emsch.routing.handler');

    $services->set('emsch.routing.handler', Handler::class)
        ->args([
            service('emsch.manager.client_request'),
            service(TemplateFactoryInterface::class),
            service('router'),
            service('profiler')->nullOnInvalid(),
        ]);

    $services->set('emsch.controller.router', RouterController::class)
        ->args([
            service('emsch.routing.handler'),
            service('ems_common.storage.processor'),
            service('emsch.helper_cache'),
            service('emsch.helper_exception'),
            service('http_kernel'),
        ])
        ->tag('controller.service_arguments');

    $services->set('emsch.controller.cache', CacheController::class)
        ->args([service('emsch.helper_cache')])
        ->tag('controller.service_arguments');

    $services->set('emsch.controller.form', FormController::class)
        ->args([
            service('emsch.routing.handler'),
            service('form.factory'),
        ])
        ->tag('controller.service_arguments');

    $services->set(InlineEditController::class)
        ->args([
            service('twig'),
        ])
        ->tag('controller.service_arguments');

    $services->set('emsch.controller.pdf', PdfController::class)
        ->args([
            service('emsch.routing.handler'),
            service('emsch.common.pdf_generator'),
        ])
        ->tag('controller.service_arguments');

    $services->set('emsch.controller.elasticsearch', ElasticsearchController::class)
        ->args([
            service('emsch.routing.handler'),
            service(Client::class),
        ])
        ->tag('controller.service_arguments');

    $services->set('emsch.controller.spreadsheet', SpreadsheetController::class)
        ->args([
            service('emsch.routing.handler'),
            service('ems_common.service.spreadsheet_generator_service'),
        ])
        ->tag('controller.service_arguments');

    $services->set('emsch.routing.url.generator', Generator::class)
        ->args([service(RouterInterface::class)]);

    $services->set('emsch.routing.url.transformer', Transformer::class)
        ->args([
            service('ems.twig_extension.asset'),
            service('emsch.manager.client_request'),
            service('emsch.routing.url.generator'),
            service('twig'),
            service('logger'),
            '',
        ])
        ->tag('monolog.logger', ['channel' => 'emsch_routing']);

    $services->set('emsch.twig_extension.routing', RoutingExtension::class)
        ->args([service('emsch.routing.url.transformer')])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');
};
