<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\DataCollector\ElasticaDataCollector;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->set('ems_common.data_collector.elastica', ElasticaDataCollector::class)
        ->args([
            service('ems_common.elasticsearch.elastica_logger'),
            service('ems_common.service.elastica'),
        ])
        ->tag('data_collector', ['template' => '@EMSCommon/DataCollector/elastica.html.twig', 'id' => 'elastica']);
};
