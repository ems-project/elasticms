<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Service\Submission;

use EMS\CommonBundle\Contracts\Spreadsheet\SpreadsheetGeneratorServiceInterface;
use EMS\CommonBundle\Service\ExpressionService;
use EMS\CoreBundle\Command\Submission\ExportCommand;
use EMS\CoreBundle\Command\Submission\ExportConfig;
use EMS\CoreBundle\Core\Mail\MailerService;
use EMS\CoreBundle\Service\Form\Submission\FormSubmissionService;
use EMS\Helpers\File\File;
use EMS\Helpers\File\TempFile;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Twig\Environment;

final readonly class SubmissionExporter
{
    public function __construct(
        private FormSubmissionService $formSubmissionService,
        private ExpressionService $expressionService,
        private SpreadsheetGeneratorServiceInterface $spreadsheetGeneratorService,
        private MailerService $mailerService,
        private Environment $templating,
        private PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    public function export(ExportConfig $config, SymfonyStyle $io): void
    {
        $io->section('Exporting form submissions');
        $sheet = [];

        $io->progressStart($this->formSubmissionService->count());

        foreach ($this->formSubmissionService->getUnprocessed() as $submission) {
            $data = [
                'instance' => $submission->getInstance(),
                'name' => $submission->getName(),
                'locale' => $submission->getLocale(),
                'submission_date' => $submission->getCreated()->format('c'),
                'data' => $submission->getData() ?? [],
            ];

            if ($config->filter && !$this->expressionService->evaluateToBool($config->filter, $data)) {
                $io->progressAdvance();
                continue;
            }

            $line = [];
            foreach ($config->columns as $column) {
                $line[] = $this->renderColumn($column, $data);
            }

            $sheet[] = $line;
            $io->progressAdvance();
        }

        $io->progressFinish();
        $headers = \array_column($config->columns, 'name');

        if (null === $config->filename && empty($config->emailsTo)) {
            $io->table($headers, $sheet);

            return;
        }

        if (0 === \count($sheet)) {
            $io->warning('No exported submissions found. No emails have been sent.');

            return;
        }

        $extension = $this->determineFormat($config, $io);
        $tempFile = TempFile::create();

        $this->spreadsheetGeneratorService->generateSpreadsheetFile([
            SpreadsheetGeneratorServiceInterface::SHEETS => [[
                'rows' => [[...$headers], ...$sheet],
                'name' => 'submissions',
            ]],
            SpreadsheetGeneratorServiceInterface::CONTENT_FILENAME => 'submissions',
            SpreadsheetGeneratorServiceInterface::WRITER => $extension,
        ], $tempFile->path);

        if ($config->filename) {
            File::putContents($config->filename, $tempFile->getContents());
            $io->success(\sprintf('File %s generated', $config->filename));
        }

        $io->success(\sprintf('Exported %d submissions', \count($sheet)));

        if (!empty($config->emailsTo)) {
            $this->sendEmail($tempFile, $config);
            $io->success('Email(s) sent');
        }
    }

    /**
     * @param array{field?: string, template?: string, block?: string, name?: string} $column
     * @param array<string, mixed>                                                    $data
     */
    private function renderColumn(array $column, array $data): string
    {
        if (!empty($column['field'])) {
            return $this->propertyAccessor->getValue($data, $column['field']) ?? '';
        }

        if (!empty($column['template'])) {
            $template = $this->templating->load($column['template']);

            return !empty($column['block'])
                ? $template->renderBlock($column['block'], \compact('data'))
                : $template->render(\compact('data'));
        }

        return '';
    }

    private function determineFormat(ExportConfig $config, SymfonyStyle $io): string
    {
        $fileExtension = $config->filename ? \pathinfo($config->filename, PATHINFO_EXTENSION) : null;

        if ($fileExtension && !\in_array($fileExtension, SpreadsheetGeneratorServiceInterface::FORMAT_WRITERS, true)) {
            throw new \InvalidArgumentException("Unsupported file extension: $fileExtension");
        }

        $format = $config->format ?? $fileExtension ?? SpreadsheetGeneratorServiceInterface::XLSX_WRITER;

        if ($fileExtension && $format !== $fileExtension) {
            $io->warning(\sprintf('Export format %s mismatched with file extension %s', $format, $fileExtension));
        }

        return $format;
    }

    private function sendEmail(TempFile $tempFile, ExportConfig $config): void
    {
        $mailTemplate = $this->mailerService->makeMailTemplate(ExportCommand::MAIL_TEMPLATE);

        foreach ($config->emailsTo as $email) {
            $mailTemplate->addTo($email);
        }

        $mailTemplate
            ->setSubject($config->subject)
            ->setBodyBlock('body')
            ->addAttachment($tempFile->path, \sprintf('crm-export.%s', $config->format));

        $this->mailerService->sendMailTemplate($mailTemplate);
    }
}
