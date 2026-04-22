<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Command;

use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCoreCommand extends AbstractCommand
{
    public const string OPTION_USERNAME = 'username';
    private string $username;

    public function __construct(private readonly ?string $defaultUsernameOption)
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
            null === $this->defaultUsernameOption ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL,
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

    protected function addDeprecatedUsernameOption(string $optionName = 'user', ?string $shortcut = null, ?string $default = null): void
    {
        $this->addOption(
            $optionName,
            $shortcut,
            InputOption::VALUE_REQUIRED,
            \sprintf('Deprecated, use --%s instead.', self::OPTION_USERNAME),
            $default ?? $this->defaultUsernameOption
        );
    }

    protected function handleDeprecatedUsernameOption(InputInterface $input, string $optionName = 'user', ?string $shortcut = null): void
    {
        if (!$input->hasParameterOption('--'.$optionName, true) && !($shortcut && $input->hasParameterOption('-'.$shortcut, true))) {
            return;
        }

        @\trigger_error(\sprintf('Option "--%s" is deprecated, use "--%s" instead.', $optionName, self::OPTION_USERNAME), \E_USER_DEPRECATED);
        $this->username = $this->getOptionString($optionName);
    }
}
