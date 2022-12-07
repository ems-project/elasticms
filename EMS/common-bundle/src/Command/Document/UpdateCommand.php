<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Document;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\Standard\Json;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class UpdateCommand extends AbstractCommand
{
    private const CONTENT_TYPE = 'content-type';
    private const FOLDER = 'folder';
    private const DEFAULT_FOLDER = 'document';
    private string $contentType;
    private string $folder;

    public function __construct(private readonly AdminHelper $adminHelper, string $projectFolder)
    {
        parent::__construct();
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
        $this->addArgument(self::CONTENT_TYPE, InputArgument::REQUIRED, \sprintf('Content-type\'s name to update'));
        $this->addOption(self::FOLDER, null, InputOption::VALUE_OPTIONAL, 'Folder to scan for JSON files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $coreApi = $this->adminHelper->getCoreApi();
        $dataApi = $coreApi->data($this->contentType);
        $this->io->title('Document - update');
        $this->io->section(\sprintf('Updating %s\'s documents from %s', $this->contentType, $coreApi->getBaseUrl()));

        if (!$coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run emsch:local:login', $coreApi->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $directory = \implode(DIRECTORY_SEPARATOR, [$this->folder, $this->contentType]);
        $finder = new Finder();
        $jsonFiles = $finder->in($directory)->files()->name('*.json');
        $this->io->progressStart($jsonFiles->count());
        foreach ($jsonFiles as $file) {
            if (!$file instanceof SplFileInfo) {
                throw new \RuntimeException('Unexpected SplFileInfo object');
            }
            $ouuid = $file->getBasename('.json');
            $data = Json::decode($file->getContents());
            $dataApi->save($ouuid, $data);
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        return self::EXECUTE_SUCCESS;
    }
}
