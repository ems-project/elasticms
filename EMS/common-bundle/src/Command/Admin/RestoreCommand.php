<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Command\Document\DownloadCommand;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Admin\ConfigHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;
use EMS\CommonBundle\Search\Search;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class RestoreCommand extends AbstractCommand
{
    final public const string FORCE = 'force';
    final public const string IMPORT_FOLDER = 'import-folder';
    final public const string CONFIGS_FOLDER = 'configs-folder';
    final public const string DOCUMENTS_FOLDER = 'documents-folder';
    final public const string CONFIGS_OPTION = 'configs';
    final public const string DOCUMENTS_OPTION = 'documents';
    private bool $force;
    private string $configsFolder;
    private string $documentsFolder;
    private CoreApiInterface $coreApi;
    private bool $restoreConfigsOnly;
    private bool $restoreDocumentsOnly;

    /**
     * @param string[] $excludedContentTypes
     */
    public function __construct(private readonly AdminHelper $adminHelper, string $projectFolder, private readonly array $excludedContentTypes)
    {
        parent::__construct();
        $this->configsFolder = $projectFolder.DIRECTORY_SEPARATOR.ConfigHelper::DEFAULT_FOLDER;
        $this->documentsFolder = $projectFolder.DIRECTORY_SEPARATOR.DownloadCommand::DEFAULT_FOLDER;
    }

    #[\Override]
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

    #[\Override]
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

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->coreApi = $this->adminHelper->getCoreApi();
        $this->io->title('Admin - restore');
        $this->io->section(\sprintf('Restore configurations to %s from %s for configs and from %s for documents', $this->coreApi->getBaseUrl(), $this->configsFolder, $this->documentsFolder));

        if (!$this->coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->coreApi->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        if (!$this->force) {
            $this->io->warning('Nothing will be updated, please use the --force option to really restore something');
        }

        if ($this->restoreConfigsOnly || !$this->restoreDocumentsOnly) {
            $this->restoreConfigs();
        }

        if ($this->restoreDocumentsOnly || !$this->restoreConfigsOnly) {
            $this->restoreDocuments();
        }

        if (!$this->force) {
            $this->io->warning('Nothing has been updated, please use the --force option to really restore something');
        }

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

        if ($this->force) {
            $configHelper->deleteConfigs($deleteNames);
            $configHelper->updateConfigs($updateNames);
            $configHelper->updateConfigs($addNames);
        }

        return [
            $configType,
            \sprintf('<fg=green>%d</>', \count($addNames)),
            \sprintf('<fg=blue>%d</>', \count($updateNames)),
            \sprintf('<fg=red>%d</>', \count($deleteNames)),
        ];
    }

    private function restoreDocuments(): void
    {
        $contentTypes = $this->coreApi->admin()->getContentTypes();
        $finder = new Finder();
        $finder->directories()->in($this->documentsFolder);
        foreach ($finder as $file) {
            if (\in_array($file->getFilename(), $contentTypes)) {
                continue;
            }
            $this->io->warning(\sprintf('Documents for the content type "%s" won\'t be updated, check the CMS to activate it.', $file->getFilename()));
        }

        $rows = [];
        $this->io->progressStart(\count($contentTypes));
        foreach ($contentTypes as $contentType) {
            if (\in_array($contentType, $this->excludedContentTypes)) {
                $this->io->note(\sprintf('Content type "%s" has been ignored as excluded (see EMS_EXCLUDED_CONTENT_TYPES)', $contentType));
                continue;
            }
            $rows[] = $this->restoreDocument($contentType);
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
        $this->io->table(['Content Type', 'Added', 'Updated', 'Deleted'], $rows);
    }

    /**
     * @return string[]
     */
    private function restoreDocument(string $contentType): array
    {
        $added = [];
        $knew = [];
        $updated = [];
        $deleted = [];

        $directory = \implode(DIRECTORY_SEPARATOR, [$this->documentsFolder, $contentType]);
        if (!\is_dir($directory)) {
            \mkdir($directory, 0o777, true);
        }

        $defaultAlias = $this->coreApi->meta()->getDefaultContentTypeEnvironmentAlias($contentType);
        $search = new Search([$defaultAlias]);
        $search->setContentTypes([$contentType]);

        foreach ($this->coreApi->search()->scroll($search) as $hit) {
            $filename = \implode(DIRECTORY_SEPARATOR, [$directory, $hit->getId().'.json']);
            if (!\file_exists($filename)) {
                $deleted[] = $hit->getId();
                continue;
            }
            $knew[] = $hit->getId();
            $remote = $hit->getSource(true);
            $content = \file_get_contents($filename);
            if (false === $content) {
                throw new \RuntimeException('Unexpected false content');
            }
            $local = Json::decode($content);
            if ($remote === $local) {
                continue;
            }
            $updated[] = $hit->getId();
        }

        $finder = new Finder();
        $jsonFiles = $finder->in($directory)->files()->name('*.json');
        foreach ($jsonFiles as $file) {
            $name = \pathinfo($file->getFilename(), PATHINFO_FILENAME);
            if (!\is_string($name)) {
                throw new \RuntimeException('Unexpected name type');
            }
            if (\in_array($name, $knew)) {
                continue;
            }
            $added[] = $name;
        }

        if ($this->force) {
            $coreApi = $this->adminHelper->getCoreApi();
            $dataApi = $coreApi->data($contentType);
            $this->deleteDocuments($dataApi, $deleted);
            $this->updateDocuments($dataApi, $directory, $updated);
            $this->updateDocuments($dataApi, $directory, $added);
        }

        return [
            $contentType,
            \sprintf('<fg=green>%d</>', \count($added)),
            \sprintf('<fg=blue>%d</>', \count($updated)),
            \sprintf('<fg=red>%d</>', \count($deleted)),
        ];
    }

    /**
     * @param string[] $ouuids
     */
    private function deleteDocuments(DataInterface $dataApi, array $ouuids): void
    {
        foreach ($ouuids as $ouuid) {
            if ($dataApi->delete($ouuid)) {
                continue;
            }
            throw new \RuntimeException(\sprintf('Unexpected not deleted document %s', $ouuid));
        }
    }

    /**
     * @param string[] $ouuids
     */
    private function updateDocuments(DataInterface $dataApi, string $directory, array $ouuids): void
    {
        foreach ($ouuids as $ouuid) {
            $content = \file_get_contents($directory.DIRECTORY_SEPARATOR.$ouuid.'.json');
            if (false === $content) {
                throw new \RuntimeException('Unexpected false content');
            }

            $dataApi->save($ouuid, Json::decode($content), DataInterface::MODE_REPLACE);
        }
    }
}
