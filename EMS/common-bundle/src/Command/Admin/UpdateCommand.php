<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Admin\ConfigHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\Standard\Json;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends AbstractCommand
{
    public const CONFIG_TYPE = 'config-type';
    public const ENTITY_NAME = 'entity-name';
    public const JSON_PATH = 'json-path';
    public const FOLDER = 'folder';
    private string $configType;
    private string $entityName;
    private ?string $jsonPath;
    private AdminHelper $adminHelper;
    private string $folder;

    public function __construct(AdminHelper $adminHelper, string $projectFolder)
    {
        parent::__construct();
        $this->adminHelper = $adminHelper;
        $this->folder = $projectFolder.DIRECTORY_SEPARATOR.ConfigHelper::DEFAULT_FOLDER;
    }

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->configType = $this->getArgumentString(self::CONFIG_TYPE);
        $this->entityName = $this->getArgumentString(self::ENTITY_NAME);
        $this->jsonPath = $this->getArgumentStringNull(self::JSON_PATH);
        $folder = $this->getOptionStringNull(self::FOLDER);
        if (null !== $folder) {
            $this->folder = $folder;
        }
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::CONFIG_TYPE, InputArgument::REQUIRED, 'Type of config to update');
        $this->addArgument(self::ENTITY_NAME, InputArgument::REQUIRED, 'Entity\'s name to update');
        $this->addArgument(self::JSON_PATH, InputArgument::OPTIONAL, 'Path to the JSON file');
        $this->addOption(self::FOLDER, null, InputOption::VALUE_OPTIONAL, 'Export folder');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Admin - update');
        $this->io->section(\sprintf('Updating %s:%s configuration to %s', $this->configType, $this->entityName, $this->adminHelper->getCoreApi()->getBaseUrl()));
        if (!$this->adminHelper->getCoreApi()->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }
        $configApi = $this->adminHelper->getCoreApi()->admin()->getConfig($this->configType);
        $configHelper = new ConfigHelper($configApi, $this->folder);
        if (null === $this->jsonPath) {
            $this->jsonPath = $configHelper->getFilename($this->entityName);
        }

        $fileContent = \file_get_contents($this->jsonPath);
        if (!\is_string($fileContent)) {
            throw new \RuntimeException('Unexpected non string file content');
        }
        $id = $configApi->update($this->entityName, Json::decode($fileContent));
        $this->io->section(\sprintf('%s %s with id %s has been updated', $this->configType, $this->entityName, $id));

        return self::EXECUTE_SUCCESS;
    }
}
