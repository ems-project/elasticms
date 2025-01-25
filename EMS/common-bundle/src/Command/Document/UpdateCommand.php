<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Document;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class UpdateCommand extends AbstractCommand
{
    private const string CONTENT_TYPE = 'content-type';
    private const string FOLDER = 'folder';
    private const string DEFAULT_FOLDER = 'document';
    private const string DUMP_FILE = 'dump-file';
    private const string ONLY_MISSING = 'only-missing';
    private string $contentType;
    private string $folder;
    private ?string $dumpFile = null;
    private bool $onlyMissing;

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

        $this->dumpFile = $this->getOptionStringNull(self::DUMP_FILE);
        $this->onlyMissing = $this->getOptionBool(self::ONLY_MISSING);
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::CONTENT_TYPE, InputArgument::REQUIRED, \sprintf('Content-type\'s name to update'));
        $this->addOption(self::FOLDER, null, InputOption::VALUE_OPTIONAL, 'Folder to scan for JSON files');
        $this->addOption(self::DUMP_FILE, null, InputOption::VALUE_OPTIONAL, 'Will upload the specified elasticdump file instead of the JSON files in the folder');
        $this->addOption(self::ONLY_MISSING, null, InputOption::VALUE_NONE, 'Only create missing documents');
    }

    #[\Override]
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

        if (null !== $this->dumpFile) {
            return $this->uploadDumpFile($dataApi, $this->dumpFile);
        }

        $directory = \implode(DIRECTORY_SEPARATOR, [$this->folder, $this->contentType]);
        $finder = new Finder();
        $jsonFiles = $finder->in($directory)->files()->name('*.json');
        $this->io->progressStart($jsonFiles->count());
        foreach ($jsonFiles as $file) {
            $ouuid = $file->getBasename('.json');
            if ($this->onlyMissing && $dataApi->head($ouuid)) {
                $this->io->progressAdvance();
                continue;
            }
            $data = Json::decode($file->getContents());
            $dataApi->save($ouuid, $data);
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        return self::EXECUTE_SUCCESS;
    }

    private function uploadDumpFile(DataInterface $dataApi, string $dumpFile): int
    {
        $handle = \fopen($dumpFile, 'r');
        if (false === $handle) {
            $this->io->error(\sprintf('File %s not found', $dumpFile));

            return self::EXECUTE_ERROR;
        }

        $lineCount = 0;
        while (($line = \fgets($handle)) !== false) {
            if (0 === \strlen($line)) {
                continue;
            }
            ++$lineCount;
        }
        \rewind($handle);
        $this->io->progressStart($lineCount);
        while (($line = \fgets($handle)) !== false) {
            if (0 === \strlen($line)) {
                continue;
            }
            $json = Json::decode($line);
            $ouuid = Type::string($json['_id'] ?? null);
            if ($this->onlyMissing && $dataApi->head($ouuid)) {
                $this->io->progressAdvance();
                continue;
            }
            $data = $json['_source'] ?? null;
            if (!\is_array($data)) {
                throw new \RuntimeException(\sprintf("Expect an array got '%s'", \gettype($data)));
            }
            try {
                $dataApi->save($ouuid, $data);
            } catch (\RuntimeException $e) {
                $this->io->warning(\sprintf('Error while uploading document %s: %s', $ouuid, $e->getMessage()));
            }

            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        \fclose($handle);

        return self::EXECUTE_SUCCESS;
    }
}
