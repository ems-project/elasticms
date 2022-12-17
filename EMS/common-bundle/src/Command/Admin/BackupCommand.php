<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Admin\ConfigHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BackupCommand extends AbstractCommand
{
    final public const EXPORT = 'export';
    final public const FOLDER = 'folder';
    private bool $export;
    private string $folder;
    private CoreApiInterface $coreApi;

    public function __construct(private readonly AdminHelper $adminHelper, string $projectFolder)
    {
        parent::__construct();
        $this->folder = $projectFolder.DIRECTORY_SEPARATOR.ConfigHelper::DEFAULT_FOLDER;
    }

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->export = $this->getOptionBool(self::EXPORT);
        $folder = $this->getOptionStringNull(self::FOLDER);
        if (null !== $folder) {
            $this->folder = $folder;
        }
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption(self::EXPORT, null, InputOption::VALUE_NONE, 'Backup elasticMS\'s configs in JSON files');
        $this->addOption(self::FOLDER, null, InputOption::VALUE_OPTIONAL, 'Export folder');
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

        foreach ($this->coreApi->admin()->getConfigTypes() as $configType) {
            \dump($configType);
        }

        return self::EXECUTE_SUCCESS;
    }
}
