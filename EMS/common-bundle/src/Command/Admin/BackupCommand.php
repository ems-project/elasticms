<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Command\Document\DownloadCommand;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Admin\ConfigHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Search\Search;
use EMS\CommonBundle\Storage\NotFoundException;
use EMS\Helpers\File\File;
use EMS\Helpers\File\TempFile;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpClient\Exception\ClientException;

class BackupCommand extends AbstractCommand
{
    final public const string EXPORT = 'export';
    final public const string EXPORT_FOLDER = 'export-folder';
    final public const string CONFIGS_FOLDER = 'configs-folder';
    final public const string DOCUMENTS_FOLDER = 'documents-folder';
    final public const string ASSETS_FOLDER = 'assets-folder';
    final public const string CONFIGS_OPTION = 'configs';
    final public const string DOCUMENTS_OPTION = 'documents';
    final public const string ASSETS_OPTION = 'assets';
    final public const string ASSETS_DEFAULT_FOLDER = 'assets';
    private bool $export;
    private string $configsFolder;
    private string $documentsFolder;
    private string $assetsFolder;
    private CoreApiInterface $coreApi;
    private bool $exportConfigsOnly;
    private bool $exportDocumentsOnly;
    private bool $exportAssetsOnly;
    /** @var true[] */
    private array $assetsAlreadyExported = [];
    private string $hashAlgo;

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
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminHelper->setLogger(new ConsoleLogger($output));
        $this->export = $this->getOptionBool(self::EXPORT);
        $exportFolder = $this->getOptionStringNull(self::EXPORT_FOLDER);
        if (null !== $exportFolder) {
            $this->configsFolder = $exportFolder.DIRECTORY_SEPARATOR.ConfigHelper::DEFAULT_FOLDER;
            $this->documentsFolder = $exportFolder.DIRECTORY_SEPARATOR.DownloadCommand::DEFAULT_FOLDER;
            $this->assetsFolder = $exportFolder.DIRECTORY_SEPARATOR.self::ASSETS_DEFAULT_FOLDER;
        }
        $configsFolder = $this->getOptionStringNull(self::CONFIGS_FOLDER);
        if (null !== $configsFolder) {
            $this->configsFolder = $configsFolder;
        }
        $documentsFolder = $this->getOptionStringNull(self::DOCUMENTS_FOLDER);
        if (null !== $documentsFolder) {
            $this->documentsFolder = $documentsFolder;
        }
        $assetsFolder = $this->getOptionStringNull(self::ASSETS_FOLDER);
        if (null !== $assetsFolder) {
            $this->assetsFolder = $assetsFolder;
        }
        $this->exportConfigsOnly = $this->getOptionBool(self::CONFIGS_OPTION);
        $this->exportDocumentsOnly = $this->getOptionBool(self::DOCUMENTS_OPTION);
        $this->exportAssetsOnly = $this->getOptionBool(self::ASSETS_OPTION);
        $this->hashAlgo = $this->adminHelper->getCoreApi()->file()->getHashAlgo();
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addOption(self::EXPORT, null, InputOption::VALUE_NONE, "Backup elasticMS's configs in JSON files (dry run by default)");
        $this->addOption(self::EXPORT_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Global export folder (can be overwritten per type of exports)');
        $this->addOption(self::CONFIGS_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Export configs folder');
        $this->addOption(self::DOCUMENTS_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Export documents folder');
        $this->addOption(self::ASSETS_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Export assets folder');
        $this->addOption(self::CONFIGS_OPTION, null, InputOption::VALUE_NONE, "Export elasticMS's configs only");
        $this->addOption(self::DOCUMENTS_OPTION, null, InputOption::VALUE_NONE, "Export elasticMS's documents only");
        $this->addOption(self::ASSETS_OPTION, null, InputOption::VALUE_NONE, "Export elasticMS's assets only");
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->coreApi = $this->adminHelper->getCoreApi();
        $this->io->title('Admin - backup');
        $this->io->section(\sprintf('Backup configurations from %s', $this->coreApi->getBaseUrl()));

        if (!$this->coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->coreApi->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $allFalse = !$this->exportConfigsOnly && !$this->exportDocumentsOnly && !$this->exportAssetsOnly;
        if ($this->exportConfigsOnly || $allFalse) {
            $this->exportConfigs();
        }

        if ($this->exportDocumentsOnly || $allFalse) {
            $this->exportDocuments();
        }

        if ($this->exportAssetsOnly || $allFalse) {
            $this->exportAssets();
        }

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
            \mkdir($directory, 0o777, true);
        }

        $counter = 0;
        $ouuids = [];
        foreach ($this->coreApi->search()->scroll($search) as $hit) {
            $json = Json::encode($hit->getSource(true), true);
            File::putContents(\implode(DIRECTORY_SEPARATOR, [$directory, $hit->getId().'.json']), $json);
            $ouuids[] = $hit->getId();
            ++$counter;
        }

        $finder = new Finder();
        $jsonFiles = $finder->in($directory)->files()->name('*.json');
        foreach ($jsonFiles as $file) {
            $ouuid = \pathinfo($file->getFilename(), PATHINFO_FILENAME);
            if (!\is_string($ouuid)) {
                throw new \RuntimeException('Unexpected name type');
            }
            if (\in_array($ouuid, $ouuids, true)) {
                continue;
            }
            \unlink($file->getPathname());
        }

        return $counter;
    }

    private function exportConfigs(): void
    {
        $configTypes = $this->coreApi->admin()->getConfigTypes();
        $rows = [];
        $this->io->progressStart(\count($configTypes));
        foreach ($configTypes as $configType) {
            if ('job' == $configType) {
                continue;
            }
            $rows[] = [$configType, $this->backupConfig($configType)];
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
        $this->io->table(['Config Type', '# Configs'], $rows);
    }

    private function exportDocuments(): void
    {
        $contentTypes = $this->coreApi->admin()->getContentTypes();
        $rows = [];
        $this->io->progressStart(\count($contentTypes));
        foreach ($contentTypes as $contentType) {
            if (\in_array($contentType, $this->excludedContentTypes)) {
                $this->io->note(\sprintf('Content type "%s" has been ignored as excluded (see EMS_EXCLUDED_CONTENT_TYPES)', $contentType));
                continue;
            }
            $rows[] = [$contentType, $this->backupDocuments($contentType)];
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
        $this->io->table(['Content Type', '# Documents'], $rows);
    }

    private function exportAssets(): void
    {
        $this->io->section('Download assets from configs');
        if (!\is_dir($this->assetsFolder)) {
            \mkdir($this->assetsFolder, 0o777, true);
        }
        $finder = new Finder();
        $jsonFiles = $finder->in($this->configsFolder)->in($this->documentsFolder)->files()->name('*.json');
        $this->io->progressStart($jsonFiles->count());
        foreach ($jsonFiles as $file) {
            try {
                $this->findAssets(Json::decode($file->getContents()));
            } catch (\Throwable $e) {
                $this->io->error(\sprintf('Error while parsing json file %s: %s', $file->getPathname(), $e->getMessage()));
            }
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
        
        $this->io->section('Extract assets from archives');
        $finder = new Finder();
        $files = $finder->in($this->assetsFolder)->name('*.bin');
        $this->io->progressStart($files->count());
        foreach ($files as $file) {
            $basename = $file->getBasename('.bin');
            if(!isset($this->assetsAlreadyExported[$basename])) {
                continue;
            }
            try {
                $this->findAssets(Json::decode($file->getContents()));
            } catch (\Throwable $e) {
            }
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
    }

    /**
     * @param mixed[] $json
     */
    private function findAssets(array $json): void
    {
        foreach ($json as $value) {
            if (\is_array($value)) {
                $this->findAssets($value);
            }
            if (!\is_string($value)) {
                continue;
            }
            if (1 === \preg_match(
                '/\A(?:[a-f0-9]{32}|[a-f0-9]{40}|[a-f0-9]{64}|[a-f0-9]{96}|[a-f0-9]{128})\z/i',
                $value
            )) {
                $this->extractAsset($value);
            }

            if (!\preg_match_all(
                '~(?<![a-f0-9])ems://asset:(?P<hash>[a-f0-9]{32}|[a-f0-9]{40}|[a-f0-9]{64}|[a-f0-9]{96}|[a-f0-9]{128})(?![a-f0-9])~i',
                $value,
                $matches
            )) {
                continue;
            }
            foreach ($matches['hash'] as $hash) {
                $this->extractAsset($hash);
            }
        }
    }

    private function extractAsset(string $hash): void
    {
        if (isset($this->assetsAlreadyExported[$hash])) {
            return;
        }
        $filename = $this->assetsFolder.DIRECTORY_SEPARATOR.$hash.'.bin';
        if (\file_exists($filename) && $hash === \hash_file($this->hashAlgo, $filename)) {
            $this->assetsAlreadyExported[$hash] = true;
            return;
        }
        try {
            $stream = $this->adminHelper->getCoreApi()->file()->getStream($hash);
        } catch (ClientException $e) {
            if ($e->getCode() !== 404) {
                throw $e;
            }
            $this->io->warning(\sprintf('File %s not found', $hash));

            return;
        } catch (NotFoundException) {
            $this->io->warning(\sprintf('File %s not found', $hash));

            return;
        }
        $file = TempFile::create();
        $file->loadFromStream($stream);
        if (!\rename($file->path, $filename)) {
            throw new \RuntimeException(\sprintf('Unable to save %s', $hash));
        }

        $this->assetsAlreadyExported[$hash] = true;
    }
}
