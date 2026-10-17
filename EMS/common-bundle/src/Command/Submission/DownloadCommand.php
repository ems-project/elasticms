<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Submission;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\Helpers\File\File;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::SUBMISSION_DOWNLOAD,
    description: "Forward a form submission form the admin to a form's url.",
    hidden: false
)]
class DownloadCommand extends AbstractCommand
{
    private const string OPTION_DATETIME_FROM = 'datetime-from';
    private const string OPTION_DATETIME_TO = 'datetime-to';
    private const string OPTION_NAMES = 'names';
    private const string OPTION_INSTANCES = 'instances';
    private const string OPTION_LOCALES = 'locales';
    private const string OPTION_SIZE = 'size';
    private const string ARG_OUTPUT_DIRECTORY = 'output-directory';
    /**
     * @var string[]
     */
    private array $names;
    /**
     * @var string[]
     */
    private array $instances;
    /**
     * @var string[]
     */
    private array $locales;
    private ?\DateTimeImmutable $from;
    private ?\DateTimeImmutable $to;
    private int $size;
    private string $outputDirectory;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(
                self::ARG_OUTPUT_DIRECTORY,
                InputArgument::REQUIRED,
                'Ouput directory',
            )->addOption(
                self::OPTION_DATETIME_FROM,
                null,
                InputOption::VALUE_OPTIONAL,
                'Datetime from',
            )->addOption(
                self::OPTION_DATETIME_TO,
                null,
                InputOption::VALUE_OPTIONAL,
                'Datetime to',
            )->addOption(
                self::OPTION_NAMES,
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Names filter',
            )->addOption(
                self::OPTION_INSTANCES,
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Instances filter'
            )->addOption(
                self::OPTION_LOCALES,
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Locales filter',
            )->addOption(
                self::OPTION_SIZE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Bulk size',
                '50'
            );
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->outputDirectory = $this->getArgumentString(self::ARG_OUTPUT_DIRECTORY);
        $datetimeFrom = $this->getOptionStringNull(self::OPTION_DATETIME_FROM);
        $datetimeFrom = $datetimeFrom ? \DateTimeImmutable::createFromTimestamp(Type::integer(\strtotime($datetimeFrom))) : null;
        $this->from = $datetimeFrom;
        $datetimeTo = $this->getOptionStringNull(self::OPTION_DATETIME_TO);
        $datetimeTo = $datetimeTo ? \DateTimeImmutable::createFromTimestamp(Type::integer(\strtotime($datetimeTo))) : null;
        $this->to = $datetimeTo;
        $this->names = $this->getOptionStringArray(self::OPTION_NAMES, false);
        $this->instances = $this->getOptionStringArray(self::OPTION_INSTANCES, false);
        $this->locales = $this->getOptionStringArray(self::OPTION_LOCALES, false);
        $this->size = $this->getOptionInt(self::OPTION_SIZE);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->section(\sprintf('Download submissions from %s', $this->adminHelper->getCoreApi()->getBaseUrl()));

        $from = 0;
        $total = null;
        do {
            $data = $this->adminHelper->getCoreApi()->form()->getSubmissions($from, $this->size, $this->from, $this->to, $this->names, $this->instances, $this->locales);
            if (null === $total) {
                $total = Type::integer($data['total'] ?? null);
                $this->io->progressStart($total);
            }
            foreach (Type::array($data['submissions'] ?? []) as $submission) {
                $id = Type::string($submission['id'] ?? null);
                File::putContents(\sprintf('%s%s%s.json', $this->outputDirectory, DIRECTORY_SEPARATOR, $id), Json::encode($submission, true));
                $this->io->progressAdvance();
            }
            $from += $this->size;
        } while ($from < $total);
        $this->io->progressFinish();

        return self::EXECUTE_SUCCESS;
    }
}
