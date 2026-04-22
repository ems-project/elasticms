<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Command;

use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractCoreCommand extends AbstractCommand
{
    public const string OPTION_USERNAME = 'username';
    private string $username;

    public function __construct(private readonly string $defaultUsernameOption)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addOption(
            self::OPTION_USERNAME,
            'u',
            InputOption::VALUE_NONE,
            'elasticMS\'s username',
            $this->defaultUsernameOption
        );
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->username = $this->getOptionString(self::OPTION_USERNAME);
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
