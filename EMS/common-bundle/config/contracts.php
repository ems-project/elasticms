<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiFactoryInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\Elasticsearch\QueryLoggerInterface;
use EMS\CommonBundle\Contracts\ExpressionServiceInterface;
use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use EMS\CommonBundle\Contracts\Generator\Pdf\PdfGeneratorInterface;
use EMS\CommonBundle\Contracts\Log\LocalizedLoggerFactoryInterface;
use EMS\CommonBundle\Contracts\Spreadsheet\SpreadsheetGeneratorServiceInterface;
use EMS\CommonBundle\Contracts\Twig\TemplateFactoryInterface;
use EMS\CommonBundle\Elasticsearch\ElasticaLogger;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->alias(CoreBridgeInterface::class, 'ems.core_bridge.api');

    $services->alias(CoreApiInterface::class, 'ems_common.core_api');

    $services->alias(CoreApiFactoryInterface::class, 'ems_common.core_api.factory');

    $services->alias(QueryLoggerInterface::class, ElasticaLogger::class);

    $services->alias(FileReaderInterface::class, 'ems_common.file.reader');

    $services->alias(PdfGeneratorInterface::class, 'emsch.common.pdf_generator');

    $services->alias(LocalizedLoggerFactoryInterface::class, 'ems_common.common_log.localized_logger_factory');

    $services->alias(ExpressionServiceInterface::class, 'ems_common.service.expression_service');

    $services->alias(SpreadsheetGeneratorServiceInterface::class, 'ems_common.service.spreadsheet_generator_service');

    $services->alias(TemplateFactoryInterface::class, 'ems.common.twig.template_factory');
};
