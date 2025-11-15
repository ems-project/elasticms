<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::ADMIN_WEBHOOKS_REGISTER,
    description: 'Register to Admin Webhooks.',
    hidden: false
)]
class RegisterToWebhookCommand extends AbstractCommand
{
    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $subscriptionId = $this->adminHelper->getCoreApi()->admin()->registerToWebhooks('https://site-x.example.com/webhooks/content', ['content.published']);
        $output->writeln(\sprintf('Subscription ID: %s, ', $subscriptionId));
        if ($this->io->isQuiet()) {
            echo $subscriptionId;
        }

        return self::SUCCESS;
    }
}
