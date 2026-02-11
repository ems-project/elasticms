<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\Command\Admin\BackupCommand;
use EMS\CommonBundle\Command\Admin\CommandCommand;
use EMS\CommonBundle\Command\Admin\CreateCommand;
use EMS\CommonBundle\Command\Admin\DeleteCommand;
use EMS\CommonBundle\Command\Admin\GetCommand;
use EMS\CommonBundle\Command\Admin\JobCommand;
use EMS\CommonBundle\Command\Admin\LoginCommand;
use EMS\CommonBundle\Command\Admin\NextJobCommand;
use EMS\CommonBundle\Command\Admin\RegisterToWebhookCommand;
use EMS\CommonBundle\Command\Admin\RestoreCommand;
use EMS\CommonBundle\Command\Admin\UpdateCommand;
use EMS\CommonBundle\Command\BatchCommand;
use EMS\CommonBundle\Command\CleanOrphanIndicesCommand;
use EMS\CommonBundle\Command\ClearLogsCommand;
use EMS\CommonBundle\Command\CurlCommand;
use EMS\CommonBundle\Command\Document\DownloadCommand;
use EMS\CommonBundle\Command\Document\MergeCommand;
use EMS\CommonBundle\Command\Document\PublishCommand;
use EMS\CommonBundle\Command\FileStructure\FileStructurePublishCommand;
use EMS\CommonBundle\Command\FileStructure\FileStructurePullCommand;
use EMS\CommonBundle\Command\FileStructure\FileStructurePushCommand;
use EMS\CommonBundle\Command\Info\VersionCommand;
use EMS\CommonBundle\Command\Runner\OutputCommand;
use EMS\CommonBundle\Command\Runner\StartCommand;
use EMS\CommonBundle\Command\StatusCommand;
use EMS\CommonBundle\Command\Storage\ClearCacheCommand;
use EMS\CommonBundle\Command\Storage\LoadArchiveItemsInCacheCommand;
use EMS\CommonBundle\Command\Submission\ForwardCommand;
use EMS\CommonBundle\Command\SynchronizeCommand;
use EMS\CommonBundle\Service\ElasticaService;
use EMS\CommonBundle\Storage\StorageManager;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('ems.command.batch', BatchCommand::class)
        ->args([service('twig')])
        ->tag('console.command');

    $services->set('ems.command.status', StatusCommand::class)
        ->args([
            service(ElasticaService::class),
            service(StorageManager::class),
        ])
        ->tag('console.command');

    $services->set('ems.command.curl', CurlCommand::class)
        ->args([
            service('event_dispatcher'),
            service('controller_resolver'),
            service('request_stack'),
            service('ems_common.storage.manager'),
            service('ems.twig.asset_extension'),
        ])
        ->tag('console.command');

    $services->set('ems.command.logs.clear', ClearLogsCommand::class)
        ->args([service('ems_common.repository.log')])
        ->tag('console.command');

    $services->set('ems.command.admin.login', LoginCommand::class)
        ->args([service('ems.helper.admin_api')])
        ->tag('console.command', ['command' => 'ems:admin:login']);

    $services->set('ems.command.admin.get', GetCommand::class)
        ->args([
            service('ems.helper.admin_api'),
            '%kernel.project_dir%',
        ])
        ->tag('console.command', ['command' => 'ems:admin:get']);

    $services->set('ems.command.admin.backup', BackupCommand::class)
        ->args([
            service('ems.helper.admin_api'),
            '%kernel.project_dir%',
            '%ems_common.excluded_content_types%',
        ])
        ->tag('console.command', ['command' => 'ems:admin:backup']);

    $services->set('ems.command.admin.restore', RestoreCommand::class)
        ->args([
            service('ems.helper.admin_api'),
            '%kernel.project_dir%',
            '%ems_common.excluded_content_types%',
        ])
        ->tag('console.command', ['command' => 'ems:admin:restore']);

    $services->set('ems.command.admin.update', UpdateCommand::class)
        ->args([
            service('ems.helper.admin_api'),
            '%kernel.project_dir%',
        ])
        ->tag('console.command', ['command' => 'ems:admin:update']);

    $services->set('ems.command.admin.create', CreateCommand::class)
        ->args([
            service('ems.helper.admin_api'),
            '%kernel.project_dir%',
        ])
        ->tag('console.command', ['command' => 'ems:admin:create']);

    $services->set('ems.command.admin.delete', DeleteCommand::class)
        ->args([service('ems.helper.admin_api')])
        ->tag('console.command', ['command' => 'ems:admin:delete']);

    $services->set('ems.command.admin.job', JobCommand::class)
        ->args([
            service('ems.helper.admin_api'),
            '%kernel.project_dir%',
        ])
        ->tag('console.command', ['command' => 'ems:admin:job']);

    $services->set('ems.command.admin.command', CommandCommand::class)
        ->args([service('ems.helper.admin_api')])
        ->tag('console.command');

    $services->set('ems.command.admin.next_job', NextJobCommand::class)
        ->args([
            service('ems.helper.admin_api'),
            service('ems_common.job.manager'),
        ])
        ->tag('console.command');

    $services->set('ems.command.admin.register_to_webhook', RegisterToWebhookCommand::class)
        ->args([
            service('ems.helper.admin_api'),
            service('ems.common.cache'),
        ])
        ->tag('console.command');

    $services->set('ems.command.document.download', DownloadCommand::class)
        ->args([
            service('ems.helper.admin_api'),
            '%kernel.project_dir%',
        ])
        ->tag('console.command', ['command' => 'ems:document:download']);

    $services->set('ems.command.document.publish', PublishCommand::class)
        ->args([service('ems.helper.admin_api')])
        ->tag('console.command', ['command' => 'ems:document:publish']);

    $services->set('ems.command.document.update', \EMS\CommonBundle\Command\Document\UpdateCommand::class)
        ->args([
            service('ems.helper.admin_api'),
            '%kernel.project_dir%',
        ])
        ->tag('console.command', ['command' => 'ems:document:upload']);

    $services->set('ems.command.document.merge', MergeCommand::class)
        ->args([service('ems.helper.admin_api')])
        ->tag('console.command', ['command' => 'ems:document:merge']);

    $services->set('ems.command.info.version', VersionCommand::class)
        ->args([service('ems_common.composer.info')])
        ->tag('console.command', ['command' => 'ems:version']);

    $services->set('ems.command.file_structure.publish', FileStructurePublishCommand::class)
        ->args([
            service('ems.helper.admin_api'),
            service('ems_common.storage.manager'),
        ])
        ->tag('console.command');

    $services->set('ems.command.file_structure.pull', FileStructurePullCommand::class)
        ->args([
            service('ems.helper.admin_api'),
            service('ems_common.storage.manager'),
        ])
        ->tag('console.command');

    $services->set('ems.command.file_structure.push', FileStructurePushCommand::class)
        ->args([
            service('ems.helper.admin_api'),
            service('ems_common.storage.manager'),
        ])
        ->tag('console.command');

    $services->set('ems.command.storage.clear_cache', ClearCacheCommand::class)
        ->args([service('ems_common.storage.manager')])
        ->tag('console.command');

    $services->set('ems.command.storage.load_archive_times_in_cache', LoadArchiveItemsInCacheCommand::class)
        ->args([service('ems_common.storage.manager')])
        ->tag('console.command');

    $services->set('ems.command.submission.forward', ForwardCommand::class)
        ->args([service('ems.helper.admin_api')])
        ->tag('console.command');

    $services->set('ems.command.runner.start', StartCommand::class)
        ->args([service('ems_common.runner.manager')])
        ->tag('console.command');

    $services->set('ems.command.runner.status', \EMS\CommonBundle\Command\Runner\StatusCommand::class)
        ->args([service('ems_common.runner.manager')])
        ->tag('console.command');

    $services->set('ems.command.runner.output', OutputCommand::class)
        ->args([service('ems_common.runner.manager')])
        ->tag('console.command');

    $services->set('elasticsearch:clean-orphan-indices', CleanOrphanIndicesCommand::class)
        ->args([service('http_client')])
        ->tag('console.command');

    $services->set('ems.command.indexes.synchronize', SynchronizeCommand::class)
        ->args([service('http_client')])
        ->tag('console.command');
};
