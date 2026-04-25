<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\Service\Pdf\PdfPrinterInterface;
use EMS\SubmissionBundle\Handler\DatabaseHandler;
use EMS\SubmissionBundle\Handler\EmailHandler;
use EMS\SubmissionBundle\Handler\HttpHandler;
use EMS\SubmissionBundle\Handler\MultipartHandler;
use EMS\SubmissionBundle\Handler\PdfHandler;
use EMS\SubmissionBundle\Handler\ServiceNowHandler;
use EMS\SubmissionBundle\Handler\SftpHandler;
use EMS\SubmissionBundle\Handler\SoapHandler;
use EMS\SubmissionBundle\Handler\ZipHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->defaults()
        ->private();

    $services->set('emss.handler.database', DatabaseHandler::class)
        ->args([
            service('doctrine'),
            service('emss.twig.renderer'),
            service('emss.response.transformer'),
        ])
        ->tag('emsf.handler');

    $services->set('emss.handler.email', EmailHandler::class)
        ->args([
            service('mailer'),
            service('emss.twig.renderer'),
        ])
        ->tag('emsf.handler');

    $services->set('emss.handler.http', HttpHandler::class)
        ->args([
            service(HttpClientInterface::class),
            service('emss.twig.renderer'),
            service('emss.response.transformer'),
        ])
        ->tag('emsf.handler');

    $services->set('emss.handler.multipart', MultipartHandler::class)
        ->args([
            service(HttpClientInterface::class),
            service('emss.twig.renderer'),
            service('emss.response.transformer'),
        ])
        ->tag('emsf.handler');

    $services->set('emss.handler.pdf', PdfHandler::class)
        ->args([
            service(PdfPrinterInterface::class),
            service('emss.twig.renderer'),
            service('emss.response.transformer'),
        ])
        ->tag('emsf.handler');

    $services->set('emss.handler.service_now', ServiceNowHandler::class)
        ->args([
            service(HttpClientInterface::class),
            '%emss.default_timeout%',
            service('emss.twig.renderer'),
        ])
        ->tag('emsf.handler');

    $services->set('emss.handler.sftp', SftpHandler::class)
        ->args([
            service('emss.filesystem.factory'),
            service('emss.response.transformer'),
            service('emss.twig.renderer'),
        ])
        ->tag('emsf.handler');

    $services->set('emss.handler.soap', SoapHandler::class)
        ->args([
            service('emss.twig.renderer'),
            service('emss.response.transformer'),
        ])
        ->tag('emsf.handler');

    $services->set('emss.handler.zip', ZipHandler::class)
        ->args([
            service('emss.twig.renderer'),
            service('emss.response.transformer'),
        ])
        ->tag('emsf.handler');
};
