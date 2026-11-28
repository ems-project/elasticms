<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\Command\MetricCollectCommand;
use EMS\CommonBundle\Common\Metric\EmsInfoMetricCollector;
use EMS\CommonBundle\Common\Metric\MetricCollector;
use EMS\CommonBundle\Common\Metric\MetricEventListener;
use EMS\CommonBundle\Controller\MetricController;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private()
        ->autowire(false)
        ->autoconfigure(false);

    $services->set('ems.metric.collector', MetricCollector::class)
        ->args([
            service('ems.common.cache'),
            tagged_iterator('ems.metric_collector'),
        ]);

    $services->set('ems.metric.metric_event_listener', MetricEventListener::class)
        ->args([service('ems.metric.collector')])
        ->tag('kernel.event_subscriber');

    $services->set('ems.command.metric_collect', MetricCollectCommand::class)
        ->args([service('ems.metric.collector')])
        ->tag('console.command', ['command' => 'ems:metric:collect']);

    $services->set('ems.controller.metric', MetricController::class)
        ->args([
            service('ems.metric.collector'),
            '%ems.metric.port%',
        ])
        ->call('setContainer')
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber');

    $services->set('ems.metric.ems_info_metric_collector', EmsInfoMetricCollector::class)
        ->args([service('ems_common.composer.info')])
        ->tag('ems.metric_collector');
};
