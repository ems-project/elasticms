<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Info;

use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\Composer\ComposerInfo;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends AbstractCommand
{
    private const string SHORT_NAME = 'short-name';
    private const string DEFAULT_SHORT_NAME = 'common';
    private string $shortName;

    public function __construct(private readonly ComposerInfo $composerInfo)
    {
        parent::__construct();
    }

    #[\Override]
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->shortName = $this->getArgumentString(self::SHORT_NAME);
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::SHORT_NAME, InputArgument::OPTIONAL, \sprintf('Package composer short name'), self::DEFAULT_SHORT_NAME);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $version = $this->composerInfo->getVersionPackages()[$this->shortName] ?? null;
        if (null === $version) {
            $this->io->error('Package not found');

            return self::FAILURE;
        }
        $this->io->writeln($version);

        return self::EXECUTE_SUCCESS;
    }
}
