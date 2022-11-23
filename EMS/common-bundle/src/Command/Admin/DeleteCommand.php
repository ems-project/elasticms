<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends AbstractCommand
{
    public const CONFIG_TYPE = 'config-type';
    public const ENTITY_NAME = 'entity-name';
    private string $configType;
    private string $entityName;
    private AdminHelper $adminHelper;

    public function __construct(AdminHelper $adminHelper)
    {
        parent::__construct();
        $this->adminHelper = $adminHelper;
    }

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->configType = $this->getArgumentString(self::CONFIG_TYPE);
        $this->entityName = $this->getArgumentString(self::ENTITY_NAME);
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::CONFIG_TYPE, InputArgument::REQUIRED, 'Type of config to update');
        $this->addArgument(self::ENTITY_NAME, InputArgument::REQUIRED, 'Entity\'s name to update');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Admin - delete');
        $this->io->section(\sprintf('Deleting %s:%s configuration from %s', $this->configType, $this->entityName, $this->adminHelper->getCoreApi()->getBaseUrl()));
        if (!$this->adminHelper->getCoreApi()->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }
        $configApi = $this->adminHelper->getCoreApi()->admin()->getConfig($this->configType);

        $id = $configApi->delete($this->entityName);
        $this->io->section(\sprintf('%s %s with id %s has been deleted', $this->configType, $this->entityName, $id));

        return self::EXECUTE_SUCCESS;
    }
}
