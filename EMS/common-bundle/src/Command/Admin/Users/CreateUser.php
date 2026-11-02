<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin\Users;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::ADMIN_USER_CREATE,
    description: 'Create a user.',
    hidden: false,
)]
class CreateUser extends AbstractCommand
{
    private const string ARGUMENT_USERNAME = 'username';
    private const string ARGUMENT_EMAIL = 'email';
    private const string OPTION_WYSIWYG_PROFILE = 'wysiwyg-profile';
    private const string OPTION_ROLE = 'role';
    private const string OPTION_GROUP = 'group';
    private const string OPTION_DISPLAY_NAME = 'display-name';
    private const string OPTION_LOCALE = 'locale';
    private const string OPTION_LOCALE_PREFERRED = 'locale-preferred';
    private const string OPTION_USER_OPTIONS = 'user-options';

    public function __construct(
        private readonly AdminHelper $adminHelper,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument(self::ARGUMENT_USERNAME, InputArgument::REQUIRED, 'Username of the user')
            ->addArgument(self::ARGUMENT_EMAIL, InputArgument::REQUIRED, 'Email address of the user')
            ->addOption(self::OPTION_WYSIWYG_PROFILE, null, InputOption::VALUE_OPTIONAL, 'WYSIWYG profile name')
            ->addOption(self::OPTION_ROLE, null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'User role (repeatable)')
            ->addOption(self::OPTION_GROUP, null, InputOption::VALUE_OPTIONAL, 'Group name')
            ->addOption(self::OPTION_DISPLAY_NAME, null, InputOption::VALUE_OPTIONAL, 'Display name')
            ->addOption(self::OPTION_LOCALE, null, InputOption::VALUE_OPTIONAL, 'Locale')
            ->addOption(self::OPTION_LOCALE_PREFERRED, null, InputOption::VALUE_OPTIONAL, 'Preferred locale')
            ->addOption(self::OPTION_USER_OPTIONS, null, InputOption::VALUE_OPTIONAL, 'User options as a JSON object');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = [
            'username' => $this->getArgumentString(self::ARGUMENT_USERNAME),
            'email' => $this->getArgumentString(self::ARGUMENT_EMAIL),
        ];

        $this->addDataOption($data, self::OPTION_WYSIWYG_PROFILE, 'wysiwygProfile');
        $roles = $this->getOptionStringArray(self::OPTION_ROLE, false);
        if ([] !== $roles) {
            $data['roles'] = $roles;
        }
        $this->addDataOption($data, self::OPTION_GROUP, 'group');
        $this->addDataOption($data, self::OPTION_DISPLAY_NAME, 'displayName');
        $this->addDataOption($data, self::OPTION_LOCALE, 'locale');
        $this->addDataOption($data, self::OPTION_LOCALE_PREFERRED, 'localePreferred');

        $userOptions = $this->getOptionStringNull(self::OPTION_USER_OPTIONS);
        if (null !== $userOptions) {
            $data['userOptions'] = Json::decode($userOptions);
        }

        $url = $this->adminHelper->getCoreApi()->user()->create($data);
        if ($this->io->isQuiet()) {
            $output->write($url);
        } else {
            $this->io->success($url);
        }

        return self::EXECUTE_SUCCESS;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function addDataOption(array &$data, string $option, string $field): void
    {
        $value = $this->getOptionStringNull($option);
        if (null !== $value) {
            $data[$field] = $value;
        }
    }
}
