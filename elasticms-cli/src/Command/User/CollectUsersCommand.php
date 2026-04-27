<?php

declare(strict_types=1);

namespace App\CLI\Command\User;

use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\Spreadsheet\SpreadsheetGeneratorServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
    public const string OPTION_FILENAME = 'filename';

    /**
     * @var string[]
     */
    public const array TABLE_HEADER = [
        'Admin',
        'Username',
        'Email',
        'Email Domain',
        'Display Name',
        'Last Login',
        'Expiration Date',
        'Roles',
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
    private ?string $filename = null;

    public function __construct(private readonly AdminHelper $adminHelper, private readonly SpreadsheetGeneratorServiceInterface $spreadsheetGeneratorService)
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
            )->addOption(
                self::OPTION_FILENAME,
                null,
                InputOption::VALUE_OPTIONAL,
                'Export filename, xlsx or csv formats are supported',
            );
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminUrls = $this->getArgumentStringArray(self::ADMIN_URLS_ARGUMENT);
        $this->username = $this->getArgumentString(self::USERNAME_ARGUMENT);
        $this->filename = $this->getOptionStringNull(self::OPTION_FILENAME);
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

        if (null == $this->filename) {
            $this->io->table(self::TABLE_HEADER, $this->users);

            return self::SUCCESS;
        }

        $fileExtension = \pathinfo($this->filename, PATHINFO_EXTENSION);
        if (!\in_array($fileExtension, SpreadsheetGeneratorServiceInterface::FORMAT_WRITERS, true)) {
            $this->io->error(\sprintf('File extension %s is not supported', $fileExtension));

            return self::INVALID;
        }

        $config = [
            SpreadsheetGeneratorServiceInterface::SHEETS => [[
                'rows' => [self::TABLE_HEADER, ...$this->users],
                'name' => 'users',
            ]],
            SpreadsheetGeneratorServiceInterface::CONTENT_FILENAME => 'users',
            SpreadsheetGeneratorServiceInterface::WRITER => $fileExtension,
        ];
        $this->spreadsheetGeneratorService->generateSpreadsheetFile($config, $this->filename);
        $this->io->success(\sprintf('Collected %s users in the file %s', \count($this->users), $this->filename));

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
                $profile->getLastLogin()?->format('Y-m-d H:i:s') ?? '',
                $profile->getExpirationDate()?->format('Y-m-d H:i:s') ?? '',
                \implode(', ', $profile->getRoles()),
            ];
        }
    }
}
