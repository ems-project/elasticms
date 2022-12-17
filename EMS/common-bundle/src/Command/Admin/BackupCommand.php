<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Command\Document\DownloadCommand;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Admin\ConfigHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\Standard\Json;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Search\Search;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BackupCommand extends AbstractCommand
{
    final public const EXPORT = 'export';
    final public const CONFIGS_FOLDER = 'configs-folder';
    final public const DOCUMENTS_FOLDER = 'documents-folder';
    private bool $export;
    private string $configsFolder;
    private string $documentsFolder;
    private CoreApiInterface $coreApi;

    public function __construct(private readonly AdminHelper $adminHelper, string $projectFolder)
    {
        parent::__construct();
        $this->configsFolder = $projectFolder.DIRECTORY_SEPARATOR.ConfigHelper::DEFAULT_FOLDER;
        $this->documentsFolder = $projectFolder.DIRECTORY_SEPARATOR.DownloadCommand::DEFAULT_FOLDER;
    }

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->export = $this->getOptionBool(self::EXPORT);
        $configsFolder = $this->getOptionStringNull(self::CONFIGS_FOLDER);
        if (null !== $configsFolder) {
            $this->configsFolder = $configsFolder;
        }
        $documentsFolder = $this->getOptionStringNull(self::DOCUMENTS_FOLDER);
        if (null !== $documentsFolder) {
            $this->documentsFolder = $documentsFolder;
        }
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption(self::EXPORT, null, InputOption::VALUE_NONE, 'Backup elasticMS\'s configs in JSON files');
        $this->addOption(self::CONFIGS_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Export configs folder');
        $this->addOption(self::DOCUMENTS_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Export documents folder');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->coreApi = $this->adminHelper->getCoreApi();
        $this->io->title('Admin - backup');
        $this->io->section(\sprintf('Backup configurations from %s', $this->coreApi->getBaseUrl()));

        if (!$this->coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->coreApi->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $configTypes = $this->coreApi->admin()->getConfigTypes();
        $rows = [];
        $this->io->progressStart(\count($configTypes));
        foreach ($configTypes as $configType) {
            $rows[] = [$configType, $this->backupConfig($configType)];
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
        $this->io->table(['Config Type', '# Configs'], $rows);

        $contentTypes = $this->coreApi->admin()->getContentTypes();
        $rows = [];
        $this->io->progressStart(\count($contentTypes));
        foreach ($contentTypes as $contentType) {
            $rows[] = [$contentType, $this->backupDocuments($contentType)];
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
        $this->io->table(['Content Type', '# Documents'], $rows);

        return self::EXECUTE_SUCCESS;
    }

    private function backupConfig(string $configType): int
    {
        $configApi = $this->coreApi->admin()->getConfig($configType);
        $configHelper = new ConfigHelper($configApi, $this->configsFolder);

        if ($this->export) {
            $configHelper->update();
        }

        return \count($configApi->index());
    }

    private function backupDocuments(string $contentType): int
    {
        $defaultAlias = $this->coreApi->meta()->getDefaultContentTypeEnvironmentAlias($contentType);
        $search = new Search([$defaultAlias]);
        $search->setContentTypes([$contentType]);

        $directory = \implode(DIRECTORY_SEPARATOR, [$this->documentsFolder, $contentType]);
        if (!\is_dir($directory)) {
            \mkdir($directory, 0777, true);
        }

        $counter = 0;
        foreach ($this->coreApi->search()->scroll($search) as $hit) {
            $json = Json::encode($hit->getSource(true), true);
            \file_put_contents(\implode(DIRECTORY_SEPARATOR, [$directory, $hit->getId().'.json']), $json);
            ++$counter;
        }

        return $counter;
    }
}
