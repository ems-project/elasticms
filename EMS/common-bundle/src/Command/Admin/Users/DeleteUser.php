<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin\Users;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::ADMIN_USER_DELETE,
    description: 'Delete an admin user.',
    hidden: false,
)]
class DeleteUser extends AbstractCommand
{
    private const string ARGUMENT_USERNAME = 'username';

    public function __construct(
        private readonly AdminHelper $adminHelper,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::ARGUMENT_USERNAME, InputArgument::REQUIRED, 'Username or email of the user');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $this->getArgumentString(self::ARGUMENT_USERNAME);
        $this->adminHelper->getCoreApi()->user()->delete($username);

        if ($this->io->isQuiet()) {
            $output->write($username);
        } else {
            $this->io->success(\sprintf('User "%s" has been deleted.', $username));
        }

        return self::EXECUTE_SUCCESS;
    }
}
