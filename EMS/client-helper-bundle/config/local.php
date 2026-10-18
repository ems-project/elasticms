<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\ClientHelperBundle\Command\Local\AbstractLocalCommand;
use EMS\ClientHelperBundle\Command\Local\FolderUploadCommand;
use EMS\ClientHelperBundle\Command\Local\LoginCommand;
use EMS\ClientHelperBundle\Command\Local\PullCommand;
use EMS\ClientHelperBundle\Command\Local\PushCommand;
use EMS\ClientHelperBundle\Command\Local\StatusCommand;
use EMS\ClientHelperBundle\Command\Local\UploadAssetsCommand;
use EMS\ClientHelperBundle\Helper\Local\LocalEnvironmentFactory;
use EMS\ClientHelperBundle\Helper\Local\LocalHelper;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('emsch.helper.local_environment_factory', LocalEnvironmentFactory::class)
        ->args([
            '%kernel.project_dir%',
            '%emsch.local.path%',
        ]);

    $services->set('emsch.helper.local', LocalHelper::class)
        ->args([
            service('ems_common.core_api.token_store'),
            service('emsch.manager.client_request'),
            service('emsch.helper_content_type'),
            service('emsch.helper.builders'),
            service('ems_common.core_api'),
            service('logger'),
            '%kernel.project_dir%',
        ]);

    $services->set('emsch.command.local', AbstractLocalCommand::class)
        ->private()
        ->abstract()
        ->args([
            service('emsch.helper_environment'),
            service('emsch.helper.local'),
        ]);

    $services->set('emsch.command.local.login', LoginCommand::class)
        ->parent('emsch.command.local')
        ->tag('console.command', ['command' => 'emsch:local:login']);

    $services->set('emsch.command.local.pull', PullCommand::class)
        ->parent('emsch.command.local')
        ->tag('console.command', ['command' => 'emsch:local:pull']);

    $services->set('emsch.command.local.push', PushCommand::class)
        ->parent('emsch.command.local')
        ->tag('console.command', ['command' => 'emsch:local:push']);

    $services->set('emsch.command.local.status', StatusCommand::class)
        ->parent('emsch.command.local')
        ->tag('console.command', ['command' => 'emsch:local:status']);

    $services->set('emsch.command.local.upload_assets', UploadAssetsCommand::class)
        ->parent('emsch.command.local')
        ->args(['%emsch.asset_local_folder%'])
        ->tag('console.command', ['command' => 'emsch:local:upload-assets']);

    $services->set('emsch.command.local.folder_upload', FolderUploadCommand::class)
        ->parent('emsch.command.local')
        ->tag('console.command', ['command' => 'emsch:local:folder-upload']);
};
