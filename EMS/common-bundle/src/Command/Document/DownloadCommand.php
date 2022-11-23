<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Document;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\Standard\Json;
use EMS\CommonBundle\Search\Search;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommand extends AbstractCommand
{
    private const CONTENT_TYPE = 'content-type';
    private const FOLDER = 'folder';
    private const DEFAULT_FOLDER = 'document';
    private AdminHelper $adminHelper;
    private string $contentType;
    private string $folder;

    public function __construct(AdminHelper $adminHelper, string $projectFolder)
    {
        parent::__construct();
        $this->adminHelper = $adminHelper;
        $this->folder = $projectFolder.DIRECTORY_SEPARATOR.self::DEFAULT_FOLDER;
    }

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->contentType = $this->getArgumentString(self::CONTENT_TYPE);
        $folder = $this->getOptionStringNull(self::FOLDER);
        if (null !== $folder) {
            $this->folder = $folder;
        }
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::CONTENT_TYPE, InputArgument::REQUIRED, \sprintf('Content-type\'s name to download'));
        $this->addOption(self::FOLDER, null, InputOption::VALUE_OPTIONAL, 'Export folder');
    }

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
