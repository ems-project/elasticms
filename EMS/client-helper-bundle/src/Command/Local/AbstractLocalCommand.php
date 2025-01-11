<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command\Local;

use EMS\ClientHelperBundle\Helper\Environment\Environment;
use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use EMS\ClientHelperBundle\Helper\Local\LocalEnvironment;
use EMS\ClientHelperBundle\Helper\Local\LocalHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractLocalCommand extends AbstractCommand
{
    protected CoreApiInterface $coreApi;
    protected Environment $environment;
    protected LocalEnvironment $localEnvironment;
    protected LoggerInterface $logger;

    private const OPTION_EMSCH_ENV = 'emsch_env';

    public function __construct(protected EnvironmentHelper $environmentHelper, protected LocalHelper $localHelper)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addOption(self::OPTION_EMSCH_ENV, null, InputOption::VALUE_OPTIONAL, 'emsch env name');
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->logger = new ConsoleLogger($output);

        if (null !== $environmentName = $this->getOptionStringNull(self::OPTION_EMSCH_ENV)) {
            $environment = $this->environmentHelper->giveEnvironment($environmentName);
        } else {
            $environment = $this->environmentHelper->getEnvironmentDefault();
        }

        if (null === $environment) {
            throw new \RuntimeException(\sprintf('Environment with the name "%s" not found!', $environmentName));
        }

        $this->environment = $environment;
        $this->localEnvironment = $environment->getLocal();
        $this->localHelper->setLogger($this->logger);
        $this->coreApi = $this->localHelper->api($this->environment);
    }

    protected function healthCheck(): bool
    {
        $health = $this->localHelper->health();

        if ('red' === $health) {
            $this->io->error(\sprintf('Red health for: %s', $this->localHelper->getUrl()));

            return false;
        }

        if ('yellow' === $health) {
            $this->io->warning(\sprintf('Yellow health for %s', $this->localHelper->getUrl()));
        }

        try {
            $this->localHelper->tryIndexSearch();
        } catch (\Throwable $e) {
            $this->io->error($e->getMessage());

            return false;
        }

        return true;
    }
}
