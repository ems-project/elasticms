<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;
use EMS\CommonBundle\Storage\Processor\Processor;
use EMS\CommonBundle\Twig\AssetExtension;
use EMS\CommonBundle\Twig\CommonExtension;
use EMS\CommonBundle\Twig\CoreBridgeExtension;
use EMS\CommonBundle\Twig\HttpExtension;
use EMS\CommonBundle\Twig\InfoExtension;
use EMS\CommonBundle\Twig\ManifestExtension;
use EMS\CommonBundle\Twig\RequestExtension;
use EMS\CommonBundle\Twig\SearchExtension;
use EMS\CommonBundle\Twig\StoreDataExtension;
use EMS\CommonBundle\Twig\TemplateExtension;
use EMS\CommonBundle\Twig\TextExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->set('ems.twig_extension.asset', AssetExtension::class)
        ->args([
            service('ems_common.storage.manager'),
            service(UrlGeneratorInterface::class),
            service(Processor::class),
            service('ems_common.file.reader'),
        ])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('ems.twig_extension.common', CommonExtension::class)
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('ems.twig_extension.core_bridge', CoreBridgeExtension::class)
        ->args([service(CoreBridgeInterface::class)])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('ems.twig_extension.http', HttpExtension::class)
        ->args([
            service('http_client'),
            service('ems_common.service.http_cache_manager'),
        ])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('ems.twig_extension.info', InfoExtension::class)
        ->args([service('ems_common.composer.info')])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('ems.twig_extension.manifest', ManifestExtension::class)
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

    $services->set('ems.twig_extension.store_data', StoreDataExtension::class)
        ->args([
            service('request_stack'),
            service('ems_common.store_data.manager'),
        ])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('ems.twig_extension.template', TemplateExtension::class)
        ->args([service('twig')])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('ems.twig_extension.text', TextExtension::class)
        ->args([
            service('ems_common.text.encoder'),
            service('ems_common.json.decoder'),
            service('validator'),
            service('logger'),
        ])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');
};
