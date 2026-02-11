<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;
use EMS\CommonBundle\Storage\Processor\Processor;
use EMS\CommonBundle\Twig\AssetExtension;
use EMS\CommonBundle\Twig\CommonExtension;
use EMS\CommonBundle\Twig\CoreBridgeRuntime;
use EMS\CommonBundle\Twig\HttpClientRuntime;
use EMS\CommonBundle\Twig\InfoRuntime;
use EMS\CommonBundle\Twig\ManifestRuntime;
use EMS\CommonBundle\Twig\RequestRuntime;
use EMS\CommonBundle\Twig\StoreDataRuntime;
use EMS\CommonBundle\Twig\TemplateRuntime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->set('ems_common.twig.extension.common', CommonExtension::class)
        ->tag('twig.extension');

    $services->set('ems.twig.extension.core_bridge', CoreBridgeRuntime::class)
        ->args([service(CoreBridgeInterface::class)])
        ->tag('twig.runtime');

    $services->set('ems.twig.extension.http_client', HttpClientRuntime::class)
        ->args([service('http_client')])
        ->tag('twig.runtime');

    $services->set('ems.twig.asset_extension', AssetExtension::class)
        ->args([
            service('ems_common.storage.manager'),
            service(UrlGeneratorInterface::class),
            service(Processor::class),
            service('ems_common.file.reader'),
        ])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->alias('ems_common.twig.runtime.request', RequestRuntime::class);

    $services->set('ems_common.twig.runtime.manifest', ManifestRuntime::class)
        ->tag('twig.runtime');

    $services->set('ems_common.twig.runtime.info', InfoRuntime::class)
        ->args([service('ems_common.composer.info')])
        ->tag('twig.runtime');

    $services->set(RequestRuntime::class)
        ->args([
            service('request_stack'),
        ])
        ->tag('twig.runtime');

    $services->set(StoreDataRuntime::class)
        ->args([
            service('request_stack'),
            service('ems_common.store_data.manager'),
        ])
        ->tag('twig.runtime');

    $services->set(TemplateRuntime::class)
        ->args([service('twig')])
        ->tag('twig.runtime');
};
