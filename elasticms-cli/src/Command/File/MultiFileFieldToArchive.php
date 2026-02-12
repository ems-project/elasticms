<?php

declare(strict_types=1);

namespace App\CLI\Command\File;

use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\Helpers\File\TempFile;
use EMS\Helpers\Html\MimeTypes;
use EMS\Helpers\PropertyAccess\PropertyAccessor;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZipStream\ZipStream;

#[AsCommand(
    name: Commands::MULTI_FILES_FIELD_TO_ARCHIVE,
    description: 'Generate an archive from a multi-files field',
    hidden: false
)]
class MultiFileFieldToArchive extends AbstractCommand
{
    private const string ARG_EMS_LINK = 'ems-link';
    private const string ARG_SOURCE_PROPERTY_PATH = 'source-property-path';
    private const string ARG_TARGET_PROPERTY_PATH = 'target-property-path';
    private const string OPTION_ARCHIVE_NAME = 'archive-name';
    private const string OPTION_LAST_UPDATE_DATETIME = 'last-update';
    private const string DEFAULT_LAST_UPDATE_DATETIME = '2016-02-09T16:00:00+01:00';
    private EMSLink $emsLink;
    private string $sourcePropertyPath;
    private ?string $targetPropertyPath = null;
    private string $archiveName;
    private \DateTimeImmutable $lastUpdateDateTime;

    public function __construct(
        private readonly AdminHelper $adminHelper,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(
                self::ARG_EMS_LINK,
                InputArgument::REQUIRED,
                'EMS link to the document'
            )
            ->addArgument(
                self::ARG_SOURCE_PROPERTY_PATH,
                InputArgument::REQUIRED,
                'Property path to the multi-file field (e.g. [photos])'
            )
            ->addArgument(
                self::ARG_TARGET_PROPERTY_PATH,
                InputArgument::OPTIONAL,
                'Property path where to save the archive (e.g. [archive])'
            )
            ->addOption(
                self::OPTION_ARCHIVE_NAME,
                '',
                InputOption::VALUE_OPTIONAL,
                'Filename of the archive',
                'archive.zip'
            )
            ->addOption(
                self::OPTION_LAST_UPDATE_DATETIME,
                '',
                InputOption::VALUE_OPTIONAL,
                'Last update datetime of the archive\'s files',
                self::DEFAULT_LAST_UPDATE_DATETIME
            );
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->emsLink = $this->getArgumentEmsLink(self::ARG_EMS_LINK);
        $this->sourcePropertyPath = $this->getArgumentString(self::ARG_SOURCE_PROPERTY_PATH);
        $this->targetPropertyPath = $this->getArgumentStringNull(self::ARG_TARGET_PROPERTY_PATH);
        $this->archiveName = $this->getOptionString(self::OPTION_ARCHIVE_NAME);
        $this->lastUpdateDateTime = $this->getOptionDateTime(self::OPTION_LAST_UPDATE_DATETIME);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Multi-files fields to archive'));
        if (!$this->adminHelper->getCoreApi()->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated, run ems:admin:login'));

            return self::EXECUTE_ERROR;
        }

        $archive = TempFile::create();
        $zip = new ZipStream(
            outputStream: $archive->getOutputStream(),
            sendHttpHeaders: false,
        );

        $this->io->section('Generate the archive');
        $propertyAccessor = PropertyAccessor::createPropertyAccessor();
        $rawData = $this->adminHelper->getCoreApi()->data($this->emsLink->getContentType())->get($this->emsLink->getOuuid())->getRawData();
        $treated = [];
        $forceLastUpdateDate = self::DEFAULT_LAST_UPDATE_DATETIME === $this->lastUpdateDateTime->format('c') ? null : $this->lastUpdateDateTime;
        foreach ($propertyAccessor->iterator($this->sourcePropertyPath, $rawData) as $property => $files) {
            $this->io->section(\sprintf('Add files found in field %s', $property));
            $this->io->progressStart(\count($files));
            foreach ($files as $file) {
                $hash = Type::string($file[EmsFields::CONTENT_FILE_HASH_FIELD] ?? $file[EmsFields::CONTENT_FILE_HASH_FIELD_]);
                if (isset($treated[$hash])) {
                    continue;
                }
                $treated[$hash] = true;
                $stream = $this->adminHelper->getCoreApi()->file()->getStream($hash);
                $zip->addFileFromPsr7Stream(
                    fileName: $file[EmsFields::CONTENT_FILE_NAME_FIELD] ?? $file[EmsFields::CONTENT_FILE_NAME_FIELD_] ?? 'filename.bin',
                    stream: $stream,
                    lastModificationDateTime: $forceLastUpdateDate ?: new \DateTimeImmutable($file[EmsFields::CONTENT_FILE_DATE] ?? self::DEFAULT_LAST_UPDATE_DATETIME),
                    exactSize: Type::integer($file[EmsFields::CONTENT_FILE_SIZE_FIELD] ?? $file[EmsFields::CONTENT_FILE_SIZE_FIELD_]),
                );
                $this->io->progressAdvance();
            }
            $this->io->progressFinish();
        }
        $zip->finish();

        $this->io->section('Upload the archive');
        $progressBar = $this->io->createProgressBar($archive->getSize());
        $hash = $this->adminHelper->getCoreApi()->file()->uploadFile($archive->path, MimeTypes::APPLICATION_ZIP->value, 'archive.zip', fn (string $chunk) => $progressBar->advance(\strlen($chunk)));
        $progressBar->finish();
        $this->io->newLine();

        $baseUrl = $this->adminHelper->getCoreApi()->getBaseUrl();
        $format = \str_ends_with($baseUrl, '/') ? '%sdata/file/%s?type=%s&name=%s' : '%s/data/file/%s?type=%s&name=%s';
        $this->io->success(\sprintf($format, $this->adminHelper->getCoreApi()->getBaseUrl(), $hash, MimeTypes::APPLICATION_ZIP->value, 'archive.zip'));

        if (null === $this->targetPropertyPath) {
            return self::SUCCESS;
        }

        $algo = $this->adminHelper->getCoreApi()->file()->getHashAlgo();
        $currentTargetValue = $propertyAccessor->getValue($rawData, $this->targetPropertyPath);
        if (\is_array($currentTargetValue) && $hash === ($currentTargetValue[EmsFields::CONTENT_FILE_HASH_FIELD] ?? $currentTargetValue[EmsFields::CONTENT_FILE_HASH_FIELD_] ?? null)) {
            $this->io->success(\sprintf('The field %s was already up to date', $this->targetPropertyPath));

            return self::SUCCESS;
        }

        $propertyAccessor->setValue($rawData, $this->targetPropertyPath, [
            EmsFields::CONTENT_FILE_ALGO_FIELD_ => $algo,
            EmsFields::CONTENT_FILE_HASH_FIELD => $hash,
            EmsFields::CONTENT_FILE_HASH_FIELD_ => $hash,
            EmsFields::CONTENT_FILE_NAME_FIELD => $this->archiveName,
            EmsFields::CONTENT_FILE_NAME_FIELD_ => $this->archiveName,
            EmsFields::CONTENT_FILE_SIZE_FIELD => $archive->getSize(),
            EmsFields::CONTENT_FILE_SIZE_FIELD_ => $archive->getSize(),
            EmsFields::CONTENT_MIME_TYPE_FIELD => MimeTypes::APPLICATION_ZIP->value,
            EmsFields::CONTENT_MIME_TYPE_FIELD_ => MimeTypes::APPLICATION_ZIP->value,
        ]);
        $this->adminHelper->getCoreApi()->data($this->emsLink->getContentType())->indexAsync($this->emsLink->getOuuid(), $rawData);
        $this->io->success(\sprintf('The field %s has been updated with the archive', $this->targetPropertyPath));

        return self::SUCCESS;
    }
}
