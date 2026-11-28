<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\SubmissionBundle\Command\DatabaseStatsCommand;
use EMS\SubmissionBundle\Connection\Transformer;
use EMS\SubmissionBundle\EventSubscriber\FormSubmissionRequestSubscriber;
use EMS\SubmissionBundle\FilesystemFactory;
use EMS\SubmissionBundle\Metric\SubmissionMetricCollector;
use EMS\SubmissionBundle\Repository\FormSubmissionRepository;
use EMS\SubmissionBundle\Response\ResponseTransformer;
use EMS\SubmissionBundle\Twig\SubmissionExtension;
use EMS\SubmissionBundle\Twig\TwigRenderer;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->defaults()
        ->private();

    $services->set('emss.connection.transformer', Transformer::class)
        ->args(['%emss.connections%']);

    $services->set('emss.command.database_stats', DatabaseStatsCommand::class)
        ->args([
            service('mailer'),
            service('emss.repository.form_submission'),
        ])
        ->tag('console.command');

    $services->set('emss.event_subscriber.form_submission_request', FormSubmissionRequestSubscriber::class)
        ->args([service('emss.repository.form_submission')])
        ->tag('kernel.event_subscriber');

    $services->set('emss.repository.form_submission', FormSubmissionRepository::class)
        ->args([service('doctrine')]);

    $services->set('emss.response.transformer', ResponseTransformer::class)
        ->args([service('emss.twig.renderer')]);

    $services->set('emss.twig.renderer', TwigRenderer::class)
        ->args([service('twig')]);

    $services->set('emss.filesystem.factory', FilesystemFactory::class);

    $services->set('emss.twig_extension.submission', SubmissionExtension::class)
        ->args([service('emss.connection.transformer')])
        ->tag('twig.attribute_extension')
        ->tag('twig.runtime');

    $services->set('emss.metric.submission_metric_collector', SubmissionMetricCollector::class)
        ->args([service('emss.repository.form_submission')])
        ->tag('ems.metric_collector');
};
