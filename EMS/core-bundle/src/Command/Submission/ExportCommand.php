<?php

namespace EMS\CoreBundle\Command\Submission;

use EMS\CommonBundle\Common\Command\AbstractCommand;
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
    /** @var string[] */
    private array $fields;
    private ?string $filter;

    public function __construct(private readonly FormSubmissionService $formSubmissionService, private readonly ExpressionService $expressionService)
    {
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
                'f',
                InputOption::VALUE_OPTIONAL,
                'Expression to filter submissions'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->fields = $this->getArgumentStringArray(self::ARG_FIELDS);
        $this->filter = $this->getOptionStringNull(self::OPTIONS_FILTER);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->section('Export the form submissions');

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
            foreach ($this->fields as $field) {
                echo $data[$field] ?? '';
            }
        }

        return self::EXECUTE_SUCCESS;
    }
}
