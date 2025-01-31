<?php

declare(strict_types=1);

namespace App\CLI\Command\Web;

use App\CLI\Client\HttpClient\CacheManager;
use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Extract\Extractor;
use App\CLI\Client\WebToElasticms\Rapport\Rapport;
use App\CLI\Client\WebToElasticms\Update\UpdateManager;
use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::WEB_MIGRATION,
    description: 'Migration web resources to elaticms documents.',
    hidden: false
)]
class MigrationCommand extends AbstractCommand
{
    private const string ARG_CONFIG_FILE_PATH = 'json-path';
    private const string OPTION_CONTINUE = 'continue';
    private const string ARG_OUUID = 'OUUID';
    final public const string OPTION_CACHE_FOLDER = 'cache-folder';
    final public const string OPTION_MAX_UPDATES = 'max-updates';
    final public const string OPTION_FORCE = 'force';
    final public const string OPTION_DRY_RUN = 'dry-run';
    final public const string OPTION_DUMP = 'dump';
    final public const string OPTION_RAPPORTS_FOLDER = 'rapports-folder';
    private ConsoleLogger $logger;
    private string $jsonPath;
    private string $cacheFolder;
    private bool $force;
    private bool $continue;
    private bool $dryRun;
    private string $rapportsFolder;
    private ?string $ouuid = null;
    private bool $dump;
    private int $maxUpdate;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(
                self::ARG_CONFIG_FILE_PATH,
                InputArgument::REQUIRED,
                'Path to an config file (JSON) see documentation'
            )
            ->addOption(
                self::OPTION_CONTINUE,
                null,
                InputOption::VALUE_NONE,
                'Continue import from last know updated document'
            )
            ->addArgument(self::ARG_OUUID, InputArgument::OPTIONAL, 'ouuid', null)
            ->addOption(self::OPTION_FORCE, null, InputOption::VALUE_NONE, 'force update all documents')
            ->addOption(self::OPTION_DRY_RUN, null, InputOption::VALUE_NONE, 'don\'t update elasticms')
            ->addOption(self::OPTION_DUMP, null, InputOption::VALUE_NONE, 'dump computed arrays')
            ->addOption(self::OPTION_RAPPORTS_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Path to a folder where rapports stored', \getcwd())
            ->addOption(self::OPTION_CACHE_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Path to a folder where cache will stored', \implode(DIRECTORY_SEPARATOR, [\getcwd(), 'cache']))
            ->addOption(self::OPTION_MAX_UPDATES, null, InputOption::VALUE_OPTIONAL, 'Maximum number of document that can be updated in 1 batch (if the continue option is activated)', 1000);
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->logger = new ConsoleLogger($output);
        $this->jsonPath = $this->getArgumentString(self::ARG_CONFIG_FILE_PATH);
        $ouuid = $input->getArgument(self::ARG_OUUID);
        if (null !== $ouuid) {
            $ouuid = (string) $ouuid;
        }
        $this->ouuid = $ouuid;
        $this->force = $this->getOptionBool(self::OPTION_FORCE);
        $this->continue = $this->getOptionBool(self::OPTION_CONTINUE);
        $this->dryRun = $this->getOptionBool(self::OPTION_DRY_RUN);
        $this->dump = $this->getOptionBool(self::OPTION_DUMP);
        $this->cacheFolder = $this->getOptionString(self::OPTION_CACHE_FOLDER);
        $this->rapportsFolder = $this->getOptionString(self::OPTION_RAPPORTS_FOLDER);
        $this->maxUpdate = $this->getOptionInt(self::OPTION_MAX_UPDATES);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->adminHelper->getCoreApi()->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }
        $this->io->title('Starting updating elasticms');

        $this->io->section('Load config');
        $cacheManager = new CacheManager($this->cacheFolder);
        $configManager = $this->loadConfigManager($cacheManager);
        $rapport = new Rapport($cacheManager, $this->rapportsFolder);
        $extractor = new Extractor($configManager, $cacheManager, $this->logger, $rapport);
        $updateManager = new UpdateManager($this->adminHelper->getCoreApi(), $configManager, $this->logger, $this->dryRun);

        $this->io->section('Start cleaning');
        foreach ($configManager->getDocumentsToClean() as $contentType => $documents) {
            $contentTypeApi = $this->adminHelper->getCoreApi()->data($contentType);
            $this->io->progressStart(\count($documents));
            foreach ($documents as $document) {
                if (!$contentTypeApi->head($document)) {
                    $this->io->progressAdvance();
                    continue;
                }
                $contentTypeApi->delete($document);
                $this->io->progressAdvance();
            }
            $this->io->progressFinish();
        }

        if (!$this->continue) {
            $extractor->reset();
        }

        $this->io->section('Start updates');
        $this->io->progressStart($extractor->extractDataCount());
        $this->io->progressAdvance($extractor->currentStep());
        $counter = 0;
        $finish = true;
        foreach ($extractor->extractData($rapport, $this->ouuid) as $extractedData) {
            if ($this->dump) {
                $this->io->text(Json::encode($extractedData->getData(), true));
            }
            $updateManager->update($extractedData, $this->force, $rapport);
            $configManager->save($this->jsonPath);
            $rapport->save();
            if ($this->continue && ++$counter >= $this->maxUpdate) {
                $finish = false;
                break;
            }
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
        $this->io->writeln('');

        $this->io->section('Save config');
        $configManager->save($this->jsonPath, $finish);
        $rapport->save();

        return self::EXECUTE_SUCCESS;
    }

    protected function loadConfigManager(CacheManager $cacheManager): ConfigManager
    {
        if (!\file_exists($this->jsonPath)) {
            throw new \RuntimeException(\sprintf('Config file %s not found', $this->jsonPath));
        }
        $contents = \file_get_contents($this->jsonPath);
        if (false === $contents) {
            throw new \RuntimeException('Unexpected false config file');
        }

        return ConfigManager::deserialize($contents, $cacheManager, $this->adminHelper->getCoreApi(), $this->logger);
    }
}
