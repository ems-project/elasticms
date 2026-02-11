<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;
use EMS\CommonBundle\Storage\Processor\Processor;
use EMS\CommonBundle\Twig\AssetExtension;
use EMS\CommonBundle\Twig\CommonExtension;
use EMS\CommonBundle\Twig\CoreBridgeExtension;
use EMS\CommonBundle\Twig\HttpClientExtension;
use EMS\CommonBundle\Twig\InfoExtension;
use EMS\CommonBundle\Twig\ManifestExtension;
use EMS\CommonBundle\Twig\RequestExtension;
use EMS\CommonBundle\Twig\SearchExtension;
use EMS\CommonBundle\Twig\StoreDataRuntime;
use EMS\CommonBundle\Twig\TemplateRuntime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->set('ems_common.twig.extension.common', CommonExtension::class)
        ->tag('twig.extension');

    $services->set('ems.twig_extension.core_bridge', CoreBridgeExtension::class)
        ->args([service(CoreBridgeInterface::class)])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('ems.twig_extension.http_client', HttpClientExtension::class)
        ->args([service('http_client')])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('ems.twig_extension.asset', AssetExtension::class)
        ->args([
            service('ems_common.storage.manager'),
            service(UrlGeneratorInterface::class),
            service(Processor::class),
            service('ems_common.file.reader'),
        ])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('ems.twig_extension.manifest', ManifestExtension::class)
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('ems.twig_extension.info', InfoExtension::class)
        ->args([service('ems_common.composer.info')])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('ems.twig_extension.request', RequestExtension::class)
        ->args([
            service('request_stack'),
        ])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('ems.twig_extension.search', SearchExtension::class)
        ->args([service('ems_common.service.elastica')])
        ->tag('twig.attribute_extension')
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
