<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::ADMIN_WEBHOOKS_REGISTER,
    description: 'Register to Admin Webhooks.',
    hidden: false
)]
class RegisterToWebhookCommand extends AbstractCommand
{
    private const string ARGUMENT_ENDPOINT_URL = 'endpoint-url';
    private const string ARGUMENT_EVENTS = 'events';
    private string $endpoint;
    /**
     * @var string[]
     */
    private array $events;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument(self::ARGUMENT_ENDPOINT_URL, InputArgument::REQUIRED, 'Webhook Endpoint URL')
            ->addArgument(self::ARGUMENT_EVENTS, InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'List of Webhooks Events to register')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->endpoint = $this->getArgumentString(self::ARGUMENT_ENDPOINT_URL);
        $this->events = $this->getArgumentStringArray(self::ARGUMENT_EVENTS);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $subscriptionId = $this->adminHelper->getCoreApi()->admin()->registerToWebhooks($this->endpoint, $this->events);
        $output->writeln(\sprintf('Subscription ID: %s, ', $subscriptionId));
        if ($this->io->isQuiet()) {
            echo $subscriptionId;
        }

        return self::SUCCESS;
    }
}
