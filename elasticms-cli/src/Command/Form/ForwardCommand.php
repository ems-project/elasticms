<?php

namespace App\CLI\Command\Form;

use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ForwardCommand extends AbstractCommand
{
    protected static $defaultName = Commands::FORM_FORWARD;

    public const ARG_FORM_UUID_FROM = 'form-uuid';
    public const ARG_FORM_URL_TO = 'post-url';
    private string $fromUuid;
    private string $toUrl;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Forward a form submission form the admin to a form\'s url')
            ->addArgument(
                self::ARG_FORM_UUID_FROM,
                InputArgument::REQUIRED,
                'Source form\'s UUID'
            )->addArgument(
                self::ARG_FORM_URL_TO,
                InputArgument::REQUIRED,
                'Destination POST URL'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->fromUuid = $this->getArgumentString(self::ARG_FORM_UUID_FROM);
        $this->toUrl = $this->getArgumentString(self::ARG_FORM_URL_TO);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->adminHelper->getCoreApi()->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $this->io->section(\sprintf('Forward the form %s to %s', $this->fromUuid, $this->toUrl));

        return self::EXECUTE_SUCCESS;
    }
}
