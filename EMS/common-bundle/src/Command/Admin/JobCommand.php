<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Admin\ConfigHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class JobCommand extends AbstractCommand
{
    final public const string JOB_ID = 'job-id';
    private CoreApiInterface $coreApi;
    private string $jobIdOrJsonFile;
    private readonly string $folder;

    public function __construct(private readonly AdminHelper $adminHelper, string $projectFolder)
    {
        parent::__construct();
        $this->folder = $projectFolder.DIRECTORY_SEPARATOR.ConfigHelper::DEFAULT_FOLDER;
    }

    #[\Override]
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminHelper->setLogger(new ConsoleLogger($output));
        $this->jobIdOrJsonFile = $this->getArgumentString(self::JOB_ID);
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::JOB_ID, InputArgument::REQUIRED, 'Job\'s ID or path to a json file or to a job admin\'s file name');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->coreApi = $this->adminHelper->getCoreApi();
        $configApi = $this->coreApi->admin()->getConfig('job');
        $configHelper = new ConfigHelper($configApi, $this->folder);

        if (!$this->coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->coreApi->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $jsonPath = $configHelper->getFilename($this->jobIdOrJsonFile);
        if (\file_exists($jsonPath)) {
            $this->jobIdOrJsonFile = $jsonPath;
        }
        if (\file_exists($this->jobIdOrJsonFile)) {
            $this->io->title('Admin - Create a job');
            $content = \file_get_contents($this->jobIdOrJsonFile);
            if (false === $content) {
                throw new \RuntimeException('Unexpected false file contents');
            }
            $this->jobIdOrJsonFile = $configApi->create(Json::decode($content));
            $this->io->section(\sprintf('A job %s has been created', $this->jobIdOrJsonFile));
        }

        $this->io->title('Admin - Job\'s status');
        $this->io->section(\sprintf('Getting information about Job #%s', $this->jobIdOrJsonFile));
        $status = $this->coreApi->admin()->getJobStatus($this->jobIdOrJsonFile);
        if (!$status['done']) {
            $this->echoStatus($status);
        }
        if (!$status['started']) {
            $this->coreApi->admin()->startJob($this->jobIdOrJsonFile);
            $this->io->section(\sprintf('Job #%s has been started', $this->jobIdOrJsonFile));
        }
        $this->io->section('Job\'s output:');
        $this->coreApi->admin()->writeJobOutput($this->jobIdOrJsonFile, $this->output);

        $this->io->section('Job\'s status:');
        $this->echoStatus($this->coreApi->admin()->getJobStatus($this->jobIdOrJsonFile));

        return self::EXECUTE_SUCCESS;
    }

    /**
     * @param array{id: string, created: string, modified: string, command: string, user: string, started: bool, done: bool, output: ?string} $status
     */
    private function echoStatus(array $status): void
    {
        $this->io->definitionList(
            ['ID' => $status['id']],
            ['Created' => $status['created']],
            ['Modified' => $status['modified']],
            ['Command' => $status['command']],
            ['User' => $status['user']],
            ['Started' => $status['started'] ? 'true' : 'false'],
            ['Done' => $status['done'] ? 'true' : 'false']
        );
    }
}
