<?php

declare(strict_types=1);

namespace App\CLI\Command\MediaLibrary;

use App\CLI\Commands;
use Elastica\Query\Nested;
use Elastica\Query\Term;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Common\Spreadsheet\SpreadsheetGeneratorService;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\Spreadsheet\SpreadsheetGeneratorServiceInterface;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Search\Search;
use EMS\Helpers\File\TempFile;
use EMS\Helpers\Html\MimeTypes;
use EMS\Helpers\PropertyAccess\PropertyAccessor;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;

#[AsCommand(
    name: Commands::UPDATE_FILE_LINKS,
    description: 'Convert ems file links into ems object link',
    hidden: false
)]
final class UpdateFileLinks extends AbstractCommand
{
    private const string ARGUMENT_CONTENT_TYPE = 'content-type';
    private const string ARGUMENT_FIELDS = 'fields';
    private const string OPTION_MEDIA_LIBRARY_CONTENT_TYPE = 'content-type';
    private const string OPTION_FILE_FIELD = 'file-field';
    private const string OPTION_FORCE = 'force';
    private CoreApiInterface $coreApi;
    private string $contentTypeName;
    /** @var array<array{key: string, emsLink: EMSLink, url: string, value: string}> */
    private array $logReports;
    /** @var array<array{message: string, emsLink: EMSLink, url: string, value: string}> */
    private array $logConflictsReports;
    /** @var string[] */
    private array $fieldNames;
    private string $mediaLibraryContentTypeName;
    private string $mediaLibraryContentTypeEnvironmentAlias;
    private string $fileFieldName;
    private string $mimeType;
    private bool $force;

    public function __construct(
        private readonly AdminHelper $adminHelper,
        private readonly SpreadsheetGeneratorServiceInterface $spreadsheetGeneratorService,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_CONTENT_TYPE, InputArgument::REQUIRED, 'Content type\'s name')
            ->addArgument(self::ARGUMENT_FIELDS, InputArgument::IS_ARRAY, 'Fields to search for. Write words separated by spaces')
            ->addOption(self::OPTION_MEDIA_LIBRARY_CONTENT_TYPE, null, InputOption::VALUE_OPTIONAL, 'Media library content type\'s name', 'media_file')
            ->addOption(self::OPTION_FILE_FIELD, null, InputOption::VALUE_OPTIONAL, 'File field', 'media_file')
            ->addOption(self::OPTION_FORCE, null, InputOption::VALUE_NONE, 'Updates links. By default, only writes logs');
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->contentTypeName = $this->getArgumentString(self::ARGUMENT_CONTENT_TYPE);
        $this->fieldNames = $this->getArgumentStringArray(self::ARGUMENT_FIELDS);
        $this->mediaLibraryContentTypeName = $this->getOptionString(self::OPTION_MEDIA_LIBRARY_CONTENT_TYPE);
        $this->fileFieldName = $this->getOptionString(self::OPTION_FILE_FIELD);
        $this->coreApi = $this->adminHelper->getCoreApi();
        $this->mimeType = MimeTypes::APPLICATION_XLSX->value;
        $this->force = $input->getOption(self::OPTION_FORCE) ?? false;
        $this->logReports = [];
        $this->logConflictsReports = [];
        $this->mediaLibraryContentTypeEnvironmentAlias = $this->coreApi->meta()->getDefaultContentTypeEnvironmentAlias($this->mediaLibraryContentTypeName);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Convert ems file links into ems object link in %s', $this->contentTypeName));

        if (!$this->coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $defaultAlias = $this->coreApi->meta()->getDefaultContentTypeEnvironmentAlias($this->contentTypeName);
        $search = new Search([$defaultAlias]);
        $search->setContentTypes([$this->contentTypeName]);
        $search->setSources(\array_map(fn (string $field) => \preg_replace('/\[([^\]]+)\]/', '.$1', $field), $this->fieldNames));

        $this->io->section(\sprintf('Start analyzing %s', $this->contentTypeName));
        $this->io->progressStart($this->coreApi->search()->count($search));

        foreach ($this->coreApi->search()->scroll($search) as $hit) {
            $this->updateDocument($hit);
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        if ([] === $this->logReports && [] === $this->logConflictsReports) {
            $this->io->success('No conflicting nor asset file links found.');
        } else {
            $this->io->section('Generating log report');
            $tempFile = TempFile::create();
            $this->spreadsheetGeneratorService->generateSpreadsheetFile([
                SpreadsheetGeneratorService::CONTENT_DISPOSITION => HeaderUtils::DISPOSITION_ATTACHMENT,
                SpreadsheetGeneratorService::CONTENT_FILENAME => 'UpdateFileLinks - Rapport.xlsx',
                SpreadsheetGeneratorService::WRITER => SpreadsheetGeneratorService::XLSX_WRITER,
                SpreadsheetGeneratorService::SHEETS => [[
                    'rows' => [['Status', 'Key', 'EMSLink', 'Link', 'Value'], ...$this->logReports],
                    'name' => 'elasticMS files',
                ], [
                    'rows' => [['Conflicting documents', 'EMSLink', 'Link', 'Value'], ...$this->logConflictsReports],
                    'name' => 'Conflicting files',
                ]],
            ], $tempFile->path);
            $filename = \sprintf('UpdateFileLinks - Rapport %s.xlsx', \date('YmdHis'));
            $hash = $this->coreApi->file()->uploadFile($tempFile->path, $this->mimeType, $filename);
            $this->io->success($this->buildUrl($hash, $this->mimeType, $filename));
        }

        return self::EXECUTE_SUCCESS;
    }

    private function updateDocument(DocumentInterface $document): void
    {
        foreach ($this->fieldNames as $field) {
            $this->updateField($document, $field);
        }
    }

    private function updateField(DocumentInterface $document, string $propertyPath): void
    {
        $propertyAccessor = PropertyAccessor::createPropertyAccessor();
        $rawData = $document->getSource();
        foreach ($propertyAccessor->iterator($propertyPath, $rawData) as $property => $value) {
            $this->updateProperty($property, $value);
        }
    }

    private function updateProperty(string $key, string $value): void
    {
        if (!\preg_match_all(EMSLink::PATTERN, $value, $matches, PREG_SET_ORDER)) {
            return;
        }
        foreach ($matches as $match) {
            if ('asset' === $match['link_type']) {
                $this->replaceAssetLink($key, $match, $value);
            }
        }
    }

    /**
     * @param mixed[] $match
     */
    private function replaceAssetLink(mixed $key, array $match, mixed &$value): void
    {
        $link = EMSLink::fromMatch($match);
        $hash = $link->getOuuid();
        $found = $this->findMediaFileByHash($link, $hash, $value);
        $status = 'No Media Library object link found.';
        if ($found) {
            $status = 'Asset link found but not replaced.';
            if ($this->force) {
                $value = \str_replace($match[0], $found->jsonSerialize(), $value);
                $status = 'Existing asset link successfully replaced.';
            }
        }
        $this->logAssetLink($key, EMSLink::fromMatch($match), $value, $status);
    }

    private function findMediaFileByHash(EMSLink $link, string $hash, mixed $value): ?EMSLink
    {
        $term = new Term();
        $term->setTerm(\implode('.', [$this->fileFieldName, EmsFields::CONTENT_FILE_HASH_FIELD]), $hash);
        $nested = new Nested();
        $nested->setPath($this->fileFieldName);
        $nested->setQuery($term);
        $search = new Search([$this->mediaLibraryContentTypeEnvironmentAlias], $nested);
        $search->setContentTypes([$this->mediaLibraryContentTypeName]);
        $search->setSize(1);
        $result = $this->coreApi->search()->search($search);
        if ($result->getTotal() > 1) {
            $this->logConflict($link, $value, \sprintf('%d files(s)', $result->getTotal()));
        }
        foreach ($result->getDocuments() as $document) {
            return $document->getEmsLink();
        }

        return null;
    }

    private function logAssetLink(mixed $key, EMSLink $emsLink, string $value, string $status = ''): void
    {
        $query = $emsLink->getQuery();
        $this->logReports[] = [
            'status' => $status,
            'key' => $key,
            'emsLink' => $emsLink,
            'url' => $this->buildUrl($emsLink->getOuuid(), Type::string($query['type'] ?? MimeTypes::APPLICATION_BIN->value), Type::string($query['name'] ?? 'filename.bin')),
            'value' => $value,
        ];
    }

    private function logConflict(EMSLink $emsLink, string $value, string $message = ''): void
    {
        $query = $emsLink->getQuery();
        $this->logConflictsReports[] = [
            'message' => $message,
            'emsLink' => $emsLink,
            'url' => $this->buildUrl($emsLink->getOuuid(), Type::string($query['type'] ?? MimeTypes::APPLICATION_BIN->value), Type::string($query['name'] ?? 'filename.bin')),
            'value' => $value,
        ];
    }

    private function buildUrl(string $hash, string $type, string $filename): string
    {
        return \sprintf('%sdata/file/%s?type=%s&name=%s', $this->coreApi->getBaseUrl(), $hash, \urlencode($type), \urlencode($filename));
    }
}
