<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Command\Document\DownloadCommand;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Admin\ConfigHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreCommand extends AbstractCommand
{
    final public const FORCE = 'force';
    final public const IMPORT_FOLDER = 'import-folder';
    final public const CONFIGS_FOLDER = 'configs-folder';
    final public const DOCUMENTS_FOLDER = 'documents-folder';
    final public const CONFIGS_OPTION = 'configs';
    final public const DOCUMENTS_OPTION = 'documents';
    private bool $force;
    private string $configsFolder;
    private string $documentsFolder;
    private CoreApiInterface $coreApi;
    private bool $restoreConfigsOnly;
    private bool $restoreDocumentsOnly;

    public function __construct(private readonly AdminHelper $adminHelper, string $projectFolder)
    {
        parent::__construct();
        $this->configsFolder = $projectFolder.DIRECTORY_SEPARATOR.ConfigHelper::DEFAULT_FOLDER;
        $this->documentsFolder = $projectFolder.DIRECTORY_SEPARATOR.DownloadCommand::DEFAULT_FOLDER;
    }

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminHelper->setLogger(new ConsoleLogger($output));
        $this->force = $this->getOptionBool(self::FORCE);
        $importFolder = $this->getOptionStringNull(self::IMPORT_FOLDER);
        if (null !== $importFolder) {
            $this->configsFolder = $importFolder.DIRECTORY_SEPARATOR.ConfigHelper::DEFAULT_FOLDER;
            $this->documentsFolder = $importFolder.DIRECTORY_SEPARATOR.DownloadCommand::DEFAULT_FOLDER;
        }
        $configsFolder = $this->getOptionStringNull(self::CONFIGS_FOLDER);
        if (null !== $configsFolder) {
            $this->configsFolder = $configsFolder;
        }
        $documentsFolder = $this->getOptionStringNull(self::DOCUMENTS_FOLDER);
        if (null !== $documentsFolder) {
            $this->documentsFolder = $documentsFolder;
        }
        $this->restoreConfigsOnly = $this->getOptionBool(self::CONFIGS_OPTION);
        $this->restoreDocumentsOnly = $this->getOptionBool(self::DOCUMENTS_OPTION);
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption(self::FORCE, null, InputOption::VALUE_NONE, 'Without this option changes will be only tracked');
        $this->addOption(self::IMPORT_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Global import folder (can be overwritten per type of exports)');
        $this->addOption(self::CONFIGS_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Import configs folder');
        $this->addOption(self::DOCUMENTS_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Import documents folder');
        $this->addOption(self::CONFIGS_OPTION, null, InputOption::VALUE_NONE, 'Restore elasticMS\'s configs only');
        $this->addOption(self::DOCUMENTS_OPTION, null, InputOption::VALUE_NONE, 'Restore elasticMS\'s documents only');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->coreApi = $this->adminHelper->getCoreApi();
        $this->io->title('Admin - restore');
        $this->io->section(\sprintf('Restore configurations from %s', $this->coreApi->getBaseUrl()));

        if (!$this->coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->coreApi->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        if ($this->restoreConfigsOnly || !$this->restoreDocumentsOnly) {
            $this->restoreConfigs();
        }

//        if ($this->restoreDocumentsOnly || !$this->restoreConfigsOnly) {
//            $this->exportDocuments();
//        }

        return self::EXECUTE_SUCCESS;
    }

    private function restoreConfigs(): void
    {
        $configTypes = $this->coreApi->admin()->getConfigTypes();
        $rows = [];
        $this->io->progressStart(\count($configTypes));
        foreach ($configTypes as $configType) {
            if (\in_array($configType, ['job'])) {
                continue;
            }
            $rows[] = $this->restoreConfig($configType);
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
        $this->io->table(['Config Type', 'Added', 'Updated', 'Deleted'], $rows);
    }

    /**
     * @return string[]
     */
    private function restoreConfig(string $configType): array
    {
        $configApi = $this->coreApi->admin()->getConfig($configType);
        $configHelper = new ConfigHelper($configApi, $this->configsFolder);
        $remoteNames = $configHelper->remote();
        $localNames = $configHelper->local();

        $updateNames = \array_intersect($remoteNames, $localNames);
        $updateNames = $configHelper->needUpdate($updateNames);
        $deleteNames = \array_diff($remoteNames, $localNames);
        $addNames = \array_diff($localNames, $remoteNames);

        return [
            $configType,
            \sprintf('<fg=green>%d</>', \count($addNames)),
            \sprintf('<fg=blue>%d</>', \count($updateNames)),
            \sprintf('<fg=red>%d</>', \count($deleteNames)),
        ];
    }
}
