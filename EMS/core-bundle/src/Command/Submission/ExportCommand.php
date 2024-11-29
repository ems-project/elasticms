<?php

namespace EMS\CoreBundle\Command\Submission;

use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\SpreadsheetGeneratorServiceInterface;
use EMS\CommonBundle\Service\ExpressionService;
use EMS\CoreBundle\Commands;
use EMS\CoreBundle\Service\Form\Submission\FormSubmissionService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends AbstractCommand
{
    protected static $defaultName = Commands::SUBMISSION_EXPORT;
    public const ARG_FIELDS = 'fields';
    public const OPTIONS_FILTER = 'filter';
    public const OPTIONS_FORMAT = 'format';
    /** @var string[] */
    private array $fields;
    private ?string $filter;
    private string  $format;

    public function __construct(
        private readonly FormSubmissionService $formSubmissionService,
        private readonly ExpressionService $expressionService,
        private readonly SpreadsheetGeneratorServiceInterface $spreadsheetGeneratorService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Extract form submissions')
            ->addArgument(
                self::ARG_FIELDS,
                InputArgument::IS_ARRAY,
                'Fields to export'
            )->addOption(
                self::OPTIONS_FILTER,
                null,
                InputOption::VALUE_OPTIONAL,
                'Expression to filter submissions'
            )->addOption(
                self::OPTIONS_FORMAT,
                null,
                InputOption::VALUE_OPTIONAL,
                'File format of the export, xlsx or csv are supported',
                'xlsx'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->fields = $this->getArgumentStringArray(self::ARG_FIELDS);
        $this->filter = $this->getOptionStringNull(self::OPTIONS_FILTER);
        $this->format = $this->getOptionString(self::OPTIONS_FORMAT);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->section('Export the form submissions');
        $sheet = [[...$this->fields]];

        foreach ($this->formSubmissionService->getUnprocessed() as $submission) {
            $data = \array_merge([
                'instance' => $submission->getInstance(),
                'name' => $submission->getName(),
                'locale' => $submission->getLocale(),
                'submission_date' => $submission->getCreated()->format('c'),
            ], $submission->getData() ?? []);
            if (null !== $this->filter && !$this->expressionService->evaluateToBool($this->filter, $data)) {
                continue;
            }
            $line = [];
            foreach ($this->fields as $field) {
                $line[] = $data[$field] ?? '';
            }
            $sheet[] = $line;
        }

        $config = [
            SpreadsheetGeneratorServiceInterface::SHEETS => [[
                'rows' => $sheet,
                'name' => 'submissions',
            ]],
            SpreadsheetGeneratorServiceInterface::CONTENT_FILENAME => 'submissions',
            SpreadsheetGeneratorServiceInterface::WRITER => $this->format,
        ];
        $this->spreadsheetGeneratorService->generateSpreadsheetFile($config, 'submission.xlsx');

        return self::EXECUTE_SUCCESS;
    }
}
