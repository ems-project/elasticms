<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\Exception\NotAuthenticatedExceptionInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class LoginCommand extends AbstractCommand
{
    private const string ARG_BASE_URL = 'base-url';
    private const string OPTION_USERNAME = 'username';
    private const string OPTION_PASSWORD = 'password';
    private string $username;
    private ?string $backendUrl = null;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument(self::ARG_BASE_URL, InputArgument::OPTIONAL, 'Elasticms base url (default: EMS_BACKEND_URL)')
            ->addOption(self::OPTION_USERNAME, 'u', InputOption::VALUE_REQUIRED, 'username')
            ->addOption(self::OPTION_PASSWORD, 'p', InputOption::VALUE_REQUIRED, 'password')
        ;
    }

    #[\Override]
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null === $this->backendUrl) {
            $defaultBaseUrl = $this->adminHelper->getDefaultBaseUrl();
            $this->backendUrl = $defaultBaseUrl ?? (string) $this->io->askQuestion(new Question('Elasticms URL'));
        }

        if (null === $input->getOption(self::OPTION_USERNAME)) {
            $input->setOption(self::OPTION_USERNAME, $this->io->askQuestion(new Question('Username')));
        }
        $this->username = $this->getOptionString(self::OPTION_USERNAME);

        if (null === $input->getOption(self::OPTION_PASSWORD) && !$this->adminHelper->alreadyConnected($this->backendUrl, $this->username)) {
            $input->setOption(self::OPTION_PASSWORD, $this->io->askHidden('Password'));
        }
    }

    #[\Override]
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminHelper->setLogger(new ConsoleLogger($output));
        $this->backendUrl = $this->getArgumentStringNull(self::ARG_BASE_URL);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null === $this->backendUrl) {
            throw new \RuntimeException('Backend\'s URL not defined');
        }

        if (null === $input->getOption(self::OPTION_PASSWORD) && $this->adminHelper->alreadyConnected($this->backendUrl, $this->username)) {
            $this->io->success(\sprintf('User %s already connected on %s', $this->username, $this->adminHelper->getCoreApi()->getBaseUrl()));

            return self::EXECUTE_SUCCESS;
        }
        $this->io->title('Admin - login');

        try {
            $coreApi = $this->adminHelper->login(
                $this->backendUrl,
                $this->username,
                $this->getOptionString(self::OPTION_PASSWORD)
            );
        } catch (NotAuthenticatedExceptionInterface) {
            $this->io->error('Invalid credentials!');

            return self::EXECUTE_ERROR;
        } catch (\Throwable $e) {
            $this->io->error($e->getMessage());

            return self::EXECUTE_ERROR;
        }
        $profile = $coreApi->user()->getProfileAuthenticated();
        $this->io->success(\sprintf('Welcome %s on %s', $profile->getUsername(), $this->adminHelper->getCoreApi()->getBaseUrl()));

        return self::EXECUTE_SUCCESS;
    }
}
