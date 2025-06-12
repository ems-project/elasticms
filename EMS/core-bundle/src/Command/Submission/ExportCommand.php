<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Command\Submission;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\PropertyAccess\PropertyAccessor;
use EMS\CommonBundle\Contracts\Spreadsheet\SpreadsheetGeneratorServiceInterface;
use EMS\CommonBundle\Service\ExpressionService;
use EMS\CommonBundle\Storage\File\FileInterface;
use EMS\CommonBundle\Storage\NotFoundException;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\CoreBundle\Commands;
use EMS\CoreBundle\Core\Mail\MailerService;
use EMS\CoreBundle\Service\Form\Submission\FormSubmissionService;
use EMS\Helpers\File\File;
use EMS\Helpers\File\TempFile;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

#[AsCommand(
    name: Commands::SUBMISSION_EXPORT,
    description: 'Extract form submissions',
    hidden: false
)]
class ExportCommand extends AbstractCommand
{
    public const string MAIL_TEMPLATE = '@EMSCore/email/submissions-export.html.twig';
    public const string ARGUMENT_CONFIG_FILE = 'config-file';
    private string $configFilename;
    /** @var mixed[] */
    private array $columns;
    /** @var string[] */
    private array $fields;
    private ?string $filter = null;
    private ?string $filename = null;
    /**
     * @var string[]
     */
    private array $emailsTo;
    private string $subject;
    private ?string $format = null;

    public function __construct(
        private readonly FormSubmissionService $formSubmissionService,
        private readonly ExpressionService $expressionService,
        private readonly SpreadsheetGeneratorServiceInterface $spreadsheetGeneratorService,
        private readonly MailerService $mailerService,
        private readonly StorageManager $storageManager,
        private readonly AdminHelper $adminHelper,
        private readonly Environment $templating,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addArgument(self::ARGUMENT_CONFIG_FILE, InputArgument::REQUIRED, 'JSON config file (filename)');
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->configFilename = $this->getArgumentString(self::ARGUMENT_CONFIG_FILE);
        $config = Json::decode($this->getFile($this->configFilename)->getContent());
        
        $this->columns = $config['columns'];
        $this->fields = \array_column($config['columns'], 'field');
        $this->filter = $config['filter'];
        $this->filename = $config['filename'] ?? null;
        $this->emailsTo = $config['email-to'];
        $this->subject = $config['email-subject'];
        $this->format = $config['export-format'];
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->section('Export the form submissions');
        $sheet = [];

        $this->io->progressStart($this->formSubmissionService->count());
        $propertyAccessor = PropertyAccessor::createPropertyAccessor();
        foreach ($this->formSubmissionService->getUnprocessed() as $submission) {
            $data = [
                'instance' => $submission->getInstance(),
                'name' => $submission->getName(),
                'locale' => $submission->getLocale(),
                'submission_date' => $submission->getCreated()->format('c'),
                'data' => $submission->getData() ?? [],
            ];
            if (null !== $this->filter && !$this->expressionService->evaluateToBool($this->filter, $data)) {
                $this->io->progressAdvance();
                continue;
            }
            $line = [];
            foreach ($this->columns as $column) {
                if (!empty($column['field'])) {
                    $line[] = $propertyAccessor->getValue($data, $column['field']) ?? '';
                } elseif (!empty($column['template'])) {
                    $template = $this->templating->load($column['template']);
                    if (!empty($column['block'])) {
                        $field = $template->renderBlock($column['block'], \compact('data'));
                    } else {
                        $field = $template->render(\compact('data'));
                    }
                    $line[] = $field;
                }
            }
            $sheet[] = $line;
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
        $headers = \array_column($this->columns, 'name');
        if (null === $this->filename && empty($this->emailsTo)) {
            $this->io->table([...$headers], $sheet);

            return self::EXECUTE_SUCCESS;
        }
        $extension = $this->getFormat();

        $config = [
            SpreadsheetGeneratorServiceInterface::SHEETS => [[
                'rows' => [[...$headers], ...$sheet],
                'name' => 'submissions',
            ]],
            SpreadsheetGeneratorServiceInterface::CONTENT_FILENAME => 'submissions',
            SpreadsheetGeneratorServiceInterface::WRITER => $extension,
        ];
        $tempFile = TempFile::create();
        $this->spreadsheetGeneratorService->generateSpreadsheetFile($config, $tempFile->path);

        $this->generateFile($tempFile);
        $this->sendEmail($tempFile);
        $this->io->success('Export '.\count($sheet).' submission(s) done !');

        return self::EXECUTE_SUCCESS;
    }

    private function generateFile(TempFile $tempFile): void
    {
        if (null == $this->filename) {
            return;
        }
        File::putContents($this->filename, $tempFile->getContents());
        $this->io->success(\sprintf('The file %s has been successfully generated', $this->filename));
    }

    private function sendEmail(TempFile $tempFile): void
    {
        if (empty($this->emailsTo)) {
            return;
        }
        $mailTemplate = $this->mailerService->makeMailTemplate(self::MAIL_TEMPLATE);
        foreach ($this->emailsTo as $email) {
            $mailTemplate->addTo($email);
        }
        $mailTemplate->setSubject($this->subject);
        $mailTemplate->setBodyBlock('body');
        $mailTemplate->addAttachment($tempFile->path, \sprintf('crm-export.%s', $this->format));
        $this->mailerService->sendMailTemplate($mailTemplate);
    }

    private function getFormat(): string
    {
        $fileExtension = null;
        if (null !== $this->filename) {
            $fileExtension = \pathinfo($this->filename)['extension'] ?? null;
            if (!\in_array($fileExtension, SpreadsheetGeneratorServiceInterface::FORMAT_WRITERS, true)) {
                $this->io->error(\sprintf('File extension %s is not supported', $fileExtension));
            }
        }
        $this->format ??= $fileExtension ?? SpreadsheetGeneratorServiceInterface::XLSX_WRITER;

        if (null !== $fileExtension && $fileExtension !== $this->format) {
            $this->io->error(\sprintf('Export format %s mismatched with the file extension %s', $this->format, $this->filename));
        }

        return $this->format;
    }

    private function getFile(string $fileIdentifier): FileInterface
    {
        try {
            return $this->storageManager->getFile($fileIdentifier);
        } catch (NotFoundException) {
            return $this->adminHelper->getCoreApi()->file()->getFile($fileIdentifier);
        }
    }
}
