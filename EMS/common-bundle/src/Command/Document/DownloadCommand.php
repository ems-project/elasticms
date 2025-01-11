<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Document;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Search\Search;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommand extends AbstractCommand
{
    private const string CONTENT_TYPE = 'content-type';
    private const string FOLDER = 'folder';
    final public const string DEFAULT_FOLDER = 'document';
    private string $contentType;
    private string $folder;

    public function __construct(private readonly AdminHelper $adminHelper, string $projectFolder)
    {
        parent::__construct();
        $this->folder = $projectFolder.DIRECTORY_SEPARATOR.self::DEFAULT_FOLDER;
    }

    #[\Override]
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminHelper->setLogger(new ConsoleLogger($output));
        $this->contentType = $this->getArgumentString(self::CONTENT_TYPE);
        $folder = $this->getOptionStringNull(self::FOLDER);
        if (null !== $folder) {
            $this->folder = $folder;
        }
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::CONTENT_TYPE, InputArgument::REQUIRED, \sprintf('Content-type\'s name to download'));
        $this->addOption(self::FOLDER, null, InputOption::VALUE_OPTIONAL, 'Export folder');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $coreApi = $this->adminHelper->getCoreApi();
        $searchApi = $coreApi->search();
        $this->io->title('Document - download');
        $this->io->section(\sprintf('Getting %s\'s documents from %s', $this->contentType, $coreApi->getBaseUrl()));

        if (!$coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run emsch:local:login', $coreApi->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $defaultAlias = $coreApi->meta()->getDefaultContentTypeEnvironmentAlias($this->contentType);
        $search = new Search([$defaultAlias]);
        $search->setContentTypes([$this->contentType]);

        $directory = \implode(DIRECTORY_SEPARATOR, [$this->folder, $this->contentType]);
        if (!\is_dir($directory)) {
            \mkdir($directory, 0777, true);
        }

        $this->io->progressStart($searchApi->count($search));
        foreach ($searchApi->scroll($search) as $hit) {
            $json = Json::encode($hit->getSource(true), true);
            \file_put_contents(\implode(DIRECTORY_SEPARATOR, [$directory, $hit->getId().'.json']), $json);
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        return self::EXECUTE_SUCCESS;
    }
}
