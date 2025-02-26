<?php

declare(strict_types=1);

namespace App\CLI\Command\User;

use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::USERS_COLLECT_USERS,
    description: 'Collect user information from ElasticMS servers.',
    hidden: false
)]
class CollectUsersCommand extends AbstractCommand
{
    public const string ADMIN_URLS_ARGUMENT = 'admin-urls';
    public const string USERNAME_ARGUMENT = 'username';

    /**
     * @var string[]
     */
    public const array TABLE_HEADER = [
        'Admin',
        'Username',
        'Email',
        'Email Domain',
        'Display Name',
        'Roles',
        'Last Login',
        'Expiration Date',
    ];
    /**
     * @var string[]
     */
    private array $adminUrls;
    /**
     * @var string[][]
     */
    private array $users = [];
    private string $username;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(
                self::USERNAME_ARGUMENT,
                InputArgument::REQUIRED,
                'Username'
            )
            ->addArgument(
                self::ADMIN_URLS_ARGUMENT,
                InputArgument::IS_ARRAY,
                'List of admin URLs where to collect users'
            );
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminUrls = $this->getArgumentStringArray(self::ADMIN_URLS_ARGUMENT);
        $this->username = $this->getArgumentString(self::USERNAME_ARGUMENT);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Collect users in %d admin URLs', \count($this->adminUrls)));

        $this->io->progressStart(\count($this->adminUrls));
        foreach ($this->adminUrls as $adminUrl) {
            $coreApi = $this->login($adminUrl);
            if (null === $coreApi) {
                $this->io->progressAdvance();
                continue;
            }
            $this->collectUsers($coreApi);
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        $this->io->table(self::TABLE_HEADER, $this->users);

        return self::SUCCESS;
    }

    private function login(string $adminUrl): ?CoreApiInterface
    {
        while (!$this->adminHelper->alreadyConnected($adminUrl, $this->username)) {
            try {
                $password = $this->io->askHidden(\sprintf('%sEnter your password for %s', \PHP_EOL, $adminUrl));
                $this->adminHelper->login($adminUrl, $this->username, $password);
            } catch (\Throwable $e) {
                $this->io->error($e->getMessage());
                if (!$this->io->confirm('Do you want to continue ?')) {
                    return null;
                }
            }
        }

        return $this->adminHelper->getCoreApi();
    }

    private function collectUsers(CoreApiInterface $coreApi): void
    {
        foreach ($coreApi->user()->getProfiles() as $profile) {
            $this->users[] = [
                $coreApi->getBaseUrl(),
                $profile->getUsername(),
                $profile->getEmail(),
                \explode('@', $profile->getEmail())[1],
                $profile->getDisplayName() ?? '',
                \implode(', ', $profile->getRoles()),
                $profile->getLastLogin()?->format('Y-m-d H:i:s') ?? '',
                $profile->getExpirationDate()?->format('Y-m-d H:i:s') ?? '',
            ];
        }
    }
}
