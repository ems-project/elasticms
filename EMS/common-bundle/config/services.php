<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Ai\OpenAiService;
use EMS\CommonBundle\Common\Asset\ViteService;
use EMS\CommonBundle\Common\Bridge\Core\CoreApiBridge;
use EMS\CommonBundle\Common\Composer\ComposerInfo;
use EMS\CommonBundle\Common\Config\ConfigResolver;
use EMS\CommonBundle\Common\CoreApi\CoreApi;
use EMS\CommonBundle\Common\CoreApi\CoreApiFactory;
use EMS\CommonBundle\Common\CoreApi\TokenStore;
use EMS\CommonBundle\Common\File\FileReader;
use EMS\CommonBundle\Common\HttpCache\HttpCacheManager;
use EMS\CommonBundle\Common\HttpCache\HttpCacheRuntime;
use EMS\CommonBundle\Common\HttpCache\TagCollector;
use EMS\CommonBundle\Common\Job\JobManager;
use EMS\CommonBundle\Common\KeyStore;
use EMS\CommonBundle\Common\Route\Loader;
use EMS\CommonBundle\Common\Session\StoreDataSessionHandler;
use EMS\CommonBundle\Common\Spreadsheet\SpreadsheetGeneratorService;
use EMS\CommonBundle\Common\Twig\TemplateFactory;
use EMS\CommonBundle\Controller\ProbeController;
use EMS\CommonBundle\DependencyInjection\EnvVarProcessor\UrlEncodeEnvVarProcessor;
use EMS\CommonBundle\Elasticsearch\Client;
use EMS\CommonBundle\Elasticsearch\ElasticaFactory;
use EMS\CommonBundle\Elasticsearch\ElasticaLogger;
use EMS\CommonBundle\Elasticsearch\Mapping;
use EMS\CommonBundle\EventListener\CommandListener;
use EMS\CommonBundle\EventListener\IpAddressListener;
use EMS\CommonBundle\EventListener\TagResponseSubscriber;
use EMS\CommonBundle\Helper\Cache;
use EMS\CommonBundle\Helper\Text\Encoder;
use EMS\CommonBundle\Json\Decoder;
use EMS\CommonBundle\Service\ElasticaService;
use EMS\CommonBundle\Service\ExpressionService;
use EMS\CommonBundle\Service\Pdf\DomPdfPrinter;
use EMS\CommonBundle\Service\Pdf\PdfGenerator;
use EMS\CommonBundle\Service\Pdf\PdfPrinterInterface;
use EMS\CommonBundle\Twig\TextRuntime;
use Psr\Cache\CacheItemPoolInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->set('ems.vite', ViteService::class)
        ->args([
            service('ems_common.storage.manager'),
            service('http_client'),
            '%ems_common.vite_dev_server%',
        ]);

    $services->set('ems.core_bridge.api', CoreApiBridge::class)
        ->args([service('ems_common.core_api')]);

    $services->set('ems.config.resolver', ConfigResolver::class)
        ->args([
            service('ems_common.storage.manager'),
            service('ems.helper.admin_api'),
        ]);

    $services->set('ems_common.core_api.token_store', TokenStore::class)
        ->args([
            service(CacheItemPoolInterface::class),
            '%ems_common.backend_url%',
            '%ems_common.backend_api_key%',
        ]);

    $services->set('ems_common.core_api', CoreApi::class)
        ->factory([service('ems_common.core_api.factory'), 'create']);

    $services->set('ems_common.core_api.factory', CoreApiFactory::class)
        ->args([
            service('http_client'),
            service('logger'),
            service('ems_common.storage.manager'),
            '%ems_common.core_api.options%',
            '%ems_common.backend_url%',
            '%ems_common.backend_api_key%',
        ]);

    $services->set(ElasticaLogger::class)
        ->args([
            service('logger')->nullOnInvalid(),
            '%kernel.debug%',
        ])
        ->tag('monolog.logger', ['channel' => 'elastica']);

    $services->set(ElasticaFactory::class)
        ->args([
            service(ElasticaLogger::class),
            service('debug.stopwatch')->nullOnInvalid(),
        ]);

    $services->set(Client::class)
        ->args(['%ems_common.elasticsearch_hosts%'])
        ->factory([service(ElasticaFactory::class), 'fromConfig']);

    $services->alias(Mapping::class, 'ems_common.service.mapping');

    $services->set('ems_common.service.mapping', Mapping::class)
        ->args([service(Client::class)]);

    $services->set('ems.event_listener.command', CommandListener::class)
        ->tag('kernel.event_subscriber');

    $services->set('ems.event_listener.ip_address_listener', IpAddressListener::class)
        ->args([
            '%ems.metric.enabled%',
            '$trustedIps' => '%ems_common.request.trusted_ips%',
        ])
        ->tag('kernel.event_subscriber');

    $services->set('ems_common.text.encoder', Encoder::class)
        ->args([
            '%ems_common.slug_symbol_map%',
        ])
        ->tag('twig.runtime');

    $services->set('ems_common.helper.cache', Cache::class)
        ->args(['%ems_common.hash_algo%']);

    $services->set('ems_common.json.decoder', Decoder::class);

    $services->set('ems_common.service.expression_service', ExpressionService::class)
        ->args([service('logger')]);

    $services->set('ems_common.service.spreadsheet_generator_service', SpreadsheetGeneratorService::class)
        ->args([service('logger')]);

    $services->alias(ElasticaService::class, 'ems_common.service.elastica');

    $services->set('ems_common.service.elastica', ElasticaService::class)
        ->args([
            service('logger'),
            service(Client::class),
            service('ems.helper.admin_api'),
            service('ems_common.cache.tag_collector'),
            '%ems_common.elasticsearch_proxy_api%',
        ]);

    $services->set('ems.helper.admin_api', AdminHelper::class)
        ->args([
            service('ems_common.core_api'),
            service('ems_common.core_api.token_store'),
            service('logger'),
        ]);

    $services->set('ems_common.twig.runtime.text', TextRuntime::class)
        ->args([
            service('ems_common.text.encoder'),
            service('ems_common.json.decoder'),
            service('validator'),
            service('logger'),
        ])
        ->tag('twig.runtime');

    $services->set('ems.common.twig.htp_cache', HttpCacheRuntime::class)
        ->args([service('ems_common.service.http_cache_manager')])
        ->tag('twig.runtime');

    $services->set('ems.common.twig.template_factory', TemplateFactory::class)
        ->args([service('twig')]);

    $services->set('ems_common.file.reader', FileReader::class);

    $services->set('ems_common.pdf.printer.dom', DomPdfPrinter::class)
        ->args([
            '%kernel.project_dir%',
            '%kernel.cache_dir%',
        ]);

    $services->alias(PdfPrinterInterface::class, 'ems_common.pdf.printer.dom');

    $services->set('emsch.common.pdf_generator', PdfGenerator::class)
        ->args([service(PdfPrinterInterface::class)]);

    $services->set('urlencode.env_var_processor', UrlEncodeEnvVarProcessor::class)
        ->tag('container.env_var_processor');

    $services->set('ems.common.cache', \EMS\CommonBundle\Common\Cache\Cache::class)
        ->args([
            '%ems_common.cache_config%',
            '%kernel.cache_dir%',
        ]);

    $services->set('ems_common.route.loader', Loader::class)
        ->args(['%ems.metric.enabled%'])
        ->tag('routing.route_loader');

    $services->set('ems_common.composer.info', ComposerInfo::class)
        ->args(['%kernel.project_dir%'])
        ->call('build');

    $services->set('ems_common.job.manager', JobManager::class)
        ->args([
            service('kernel'),
            service('ems.helper.admin_api'),
        ]);

    $services->set('ems_common.controller.probe', ProbeController::class)
        ->args([service('ems_common.service.elastica')])
        ->tag('controller.service_arguments');

    $services->set('ems_common.store_data_session_handler', StoreDataSessionHandler::class)
        ->args([service('ems_common.store_data.manager')]);

    $services->set('ems_common.key_store', KeyStore::class)
        ->args(['%ems_common.key_store%']);

    $services->set('ems_common.ai.open_ai', OpenAiService::class)
        ->args([
            service('http_client'),
            service('ems_common.key_store'),
        ]);

    $services->set('ems_common.cache.tag_collector', TagCollector::class)
        ->args([
            service('request_stack'),
            '%ems_common.http_caches%',
        ]);

    $services->set('ems.event_listener.tag_response_subscriber', TagResponseSubscriber::class)
        ->args([service('ems_common.cache.tag_collector')])
        ->tag('kernel.event_subscriber');

    $services->set('ems_common.service.http_cache_manager', HttpCacheManager::class)
        ->args([
            service('logger'),
            service('ems_common.cache.tag_collector'),
        ]);
};
