<?php

declare(strict_types=1);

namespace App\CLI\Client\MediaLibrary;

use App\CLI\Helper\Tika\TikaHelper;
use Elastica\Query\Terms;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiExceptionInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;
use EMS\CommonBundle\Contracts\ExpressionServiceInterface;
use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Search\Search;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Mime\MimeTypes;

final class MediaLibrarySync
{
    public const SYNC_METADATA = '_sync_metadata';
    /** @var mixed[] */
    private array $metadatas = [];
    /** @var string[] */
    private array $knownFolders = [];
    private DataInterface $contentTypeApi;
    private string $defaultAlias;
    private MimeTypes $mimeTypes;

    public function __construct(
        private readonly string $folder,
        private readonly string $contentType,
        private readonly string $folderField,
        private readonly string $pathField,
        private readonly string $fileField,
        private readonly SymfonyStyle $io,
        private readonly bool $dryRun,
        private readonly CoreApiInterface $coreApi,
        private readonly FileReaderInterface $fileReader,
        private readonly ExpressionServiceInterface $expressionService,
        private readonly bool $onlyMissingFile,
        private readonly ?TikaHelper $tikaHelper,
        private readonly int $maxContentSize = 5120)
    {
        $this->contentTypeApi = $this->coreApi->data($this->contentType);
        $this->defaultAlias = $this->coreApi->meta()->getDefaultContentTypeEnvironmentAlias($this->contentType);
        $this->mimeTypes = new MimeTypes();
    }

    public function execute(): self
    {
        $this->io->title('MediaLibrary sync files located in a folder');

        $finder = new Finder();
        $finder->files()->in($this->folder);

        if (!$finder->hasResults()) {
            throw new \RuntimeException('No files found!');
        }

        $this->io->comment(\sprintf('%d files located', $finder->count()));

        $progressBar = $this->io->createProgressBar($finder->count());

        foreach ($finder as $file) {
            try {
                $this->uploadMediaFile($file);
            } catch (\Throwable $e) {
                $this->io->error(\sprintf('Upload failed for "%s" (%s)', $file->getRealPath(), $e->getMessage()));
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->io->newLine();

        return $this;
    }

    private function uploadMediaFile(SplFileInfo $file): void
    {
        $this->uploadMedia(DIRECTORY_SEPARATOR.$file->getRelativePathname(), [
            self::SYNC_METADATA => $this->getMetadata(DIRECTORY_SEPARATOR.$file->getRelativePathname()),
        ], $file);

        $exploded = \explode(DIRECTORY_SEPARATOR, $file->getRelativePath());
        while (\count($exploded) > 0) {
            $folder = DIRECTORY_SEPARATOR.\implode(DIRECTORY_SEPARATOR, $exploded);
            if (!\in_array($folder, $this->knownFolders)) {
                $this->uploadMedia($folder, [
                    self::SYNC_METADATA => $this->getMetadata(DIRECTORY_SEPARATOR.$folder),
                ]);
                $this->knownFolders[] = $folder;
            }
            \array_pop($exploded);
        }
    }

    /**
     * @param mixed[] $data
     */
    private function uploadMedia(string $path, array $data = [], SplFileInfo $file = null): void
    {
        $pos = \strrpos($path, DIRECTORY_SEPARATOR);
        if (false === $pos) {
            throw new \RuntimeException('Unexpected path without /');
        }
        $folder = \substr($path, 0, $pos + 1);

        $term = new Terms($this->pathField, [$path]);
        $search = new Search([$this->defaultAlias], $term->toArray());
        $search->setContentTypes([$this->contentType]);
        $result = $this->coreApi->search()->search($search);
        $document = null;
        foreach ($result->getDocuments() as $item) {
            $document = $item;
            break;
        }

        if ($this->dryRun || ($this->onlyMissingFile && null !== $document)) {
            return;
        }

        if (null !== $file) {
            $mediaFile = $document ? $document->getSource()[$this->fileField] ?? null : null;
            $data = \array_merge($data, [
                $this->fileField => $this->urlToAssetArray($file, $mediaFile),
            ]);
        }

        $data = \array_merge($data, [
            $this->folderField => $folder,
            $this->pathField => $path,
        ]);

        if (null === $document) {
            $draft = $this->contentTypeApi->create($data);
        } else {
            $draft = $this->contentTypeApi->update($document->getId(), $data);
        }

        $this->contentTypeApi->finalize($draft->getRevisionId());
    }

    /**
     * @param mixed[] $mediaFile
     *
     * @return array{sha1: string, filename: string, mimetype: string, filesize: int|null }|array{}
     */
    public function urlToAssetArray(SplFileInfo $file, ?array $mediaFile): array
    {
        $mimeType = \mime_content_type($file->getRealPath());
        $mimeType = $mimeType ?: 'application/bin';
        $hash = '';

        $filename = $file->getFilename();
        if (null == $filename) {
            $this->io->error(\sprintf('No filename for "%s"', $file->getRealPath()));

            return [];
        }

        $resource = \fopen($file->getRealPath(), 'rb');
        if (false === $resource) {
            $this->io->error(\sprintf('Not able to open filename for "%s"', $file->getRealPath()));

            return [];
        }

        $stream = new Stream($resource);
        $stream->seek(0);
        if (!$this->dryRun) {
            try {
                $hash = $this->coreApi->file()->uploadStream($stream, $file->getFilename(), $mimeType);
            } catch (CoreApiExceptionInterface $e) {
                $this->io->error(\sprintf('Asset failed for "%s" (%s)', $file->getRealPath(), $e->getMessage()));

                return [];
            }
            if (0 === \strlen($hash)) {
                $this->io->error(\sprintf('Unexpected empty hash for "%s"', $file->getRealPath()));

                return [];
            }
        }

        if (null !== $mediaFile && $mediaFile[EmsFields::CONTENT_FILE_HASH_FIELD] === $hash && !empty($mediaFile[EmsFields::CONTENT_FILE_CONTENT])) {
            return $mediaFile;
        }

        $assetArray = [
            EmsFields::CONTENT_FILE_HASH_FIELD => $hash,
            EmsFields::CONTENT_FILE_NAME_FIELD => $filename,
            EmsFields::CONTENT_MIME_TYPE_FIELD => $mimeType,
            EmsFields::CONTENT_FILE_SIZE_FIELD => $file->getSize() ? $file->getSize() : null,
        ];
        if (null === $this->tikaHelper) {
            return $assetArray;
        }

        $mimeType = $this->mimeTypes->guessMimeType($file->getRealPath());
        $promise = $this->tikaHelper->extract($stream, $mimeType);
        $promise->startText();
        $promise->startMeta();
        try {
            $assetArray[EmsFields::CONTENT_FILE_CONTENT] = \mb_substr($promise->getText(), 0, $this->maxContentSize, 'UTF-8');
            $meta = $promise->getMeta();
            $assetArray[EmsFields::CONTENT_FILE_DATE] = $meta->getCreated();
            $assetArray[EmsFields::CONTENT_FILE_AUTHOR] = $meta->getCreator();
            $assetArray[EmsFields::CONTENT_FILE_TITLE] = $meta->getTitle();
            $assetArray[EmsFields::CONTENT_FILE_LANGUAGE] = $meta->getLocale();
        } catch (\Throwable) {
        }

        return $assetArray;
    }

    public function loadMetadata(string $metadataFile, string $locateRowExpression): void
    {
        $rows = $this->fileReader->getData($metadataFile);
        $header = $rows[0] ?? [];
        $this->metadatas = [];
        foreach ($rows as $key => $value) {
            if (0 === $key) {
                continue;
            }
            $row = [];
            foreach ($value as $key => $cell) {
                $row[$header[$key] ?? $key] = $cell;
            }

            $filename = $this->expressionService->evaluateToString($locateRowExpression, [
                'row' => $row,
            ]);
            if (\is_string($filename)) {
                $this->metadatas[$filename] = $row;
            }
        }
    }

    /**
     * @return mixed[]
     */
    private function getMetadata(string $path): array
    {
        if (isset($this->metadatas[$path])) {
            return $this->metadatas[$path];
        }

        return [];
    }
}
