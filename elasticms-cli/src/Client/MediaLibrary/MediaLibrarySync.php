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
use EMS\Helpers\Standard\Text;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Mime\MimeTypes;

final class MediaLibrarySync
{
    public const string SYNC_METADATA = '_sync_metadata';
    /** @var mixed[] */
    private array $metadatas = [];
    /** @var string[] */
    private array $knownFolders = [];
    private readonly DataInterface $contentTypeApi;
    private readonly string $defaultAlias;
    private readonly MimeTypes $mimeTypes;
    private readonly Filesystem $filesystem;
    private ?TikaHelper $tikaHelper = null;
    /** @var string[] */
    private array $cleanPaths = [];

    public function __construct(
        private readonly MediaLibrarySyncOptions $options,
        private readonly SymfonyStyle $io,
        private readonly CoreApiInterface $coreApi,
        private readonly FileReaderInterface $fileReader,
        private readonly ExpressionServiceInterface $expressionService,
    ) {
        $this->contentTypeApi = $this->coreApi->data($this->options->contentType);
        $this->defaultAlias = $this->coreApi->meta()->getDefaultContentTypeEnvironmentAlias($this->options->contentType);
        $this->mimeTypes = new MimeTypes();
        $this->filesystem = new Filesystem();
    }

    public function setTikaHelper(?TikaHelper $tikaHelper): void
    {
        $this->tikaHelper = $tikaHelper;
    }

    public function execute(): self
    {
        $this->io->title('MediaLibrary sync files located in a folder');

        if (null !== $this->options->metaDataFile) {
            $this->loadMetadata($this->options->metaDataFile);
        }

        $finder = new Finder();
        $finder->files()->in($this->getFolderPath());

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

        $this->filesystem->remove($this->cleanPaths);

        return $this;
    }

    private function uploadMediaFile(SplFileInfo $file): void
    {
        $path = $this->options->targetFolder.$file->getRelativePathname();
        $metaData = $this->getMetadata($path);

        if ($this->options->onlyMetadataFile && 0 === \count($metaData)) {
            $this->io->warning(\sprintf('Skipped "%s" with reason metadata was not found', $path));

            return;
        }

        $this->uploadMedia($path, [self::SYNC_METADATA => $metaData], $file);

        $exploded = \explode('/', $file->getRelativePath());
        while (\count($exploded) > 0) {
            $folder = '/'.\implode('/', $exploded);
            if (!\in_array($folder, $this->knownFolders)) {
                $this->uploadMedia($folder, [
                    self::SYNC_METADATA => $this->getMetadata('/'.$folder),
                ]);
                $this->knownFolders[] = $folder;
            }
            \array_pop($exploded);
        }
    }

    /**
     * @param mixed[] $data
     */
    private function uploadMedia(string $path, array $data = [], ?SplFileInfo $file = null): void
    {
        $pos = \strrpos($path, '/');
        if (false === $pos) {
            throw new \RuntimeException('Unexpected path without /');
        }
        $folder = \substr($path, 0, $pos + 1);

        $term = new Terms($this->options->pathField, [$path]);
        $search = new Search([$this->defaultAlias], $term->toArray());
        $search->setContentTypes([$this->options->contentType]);
        $result = $this->coreApi->search()->search($search);
        $document = null;
        foreach ($result->getDocuments() as $item) {
            $document = $item;
            break;
        }

        if ($this->options->dryRun || ($this->options->onlyMissingFile && null !== $document)) {
            return;
        }

        if (null !== $file) {
            if ($this->io->isVerbose()) {
                $this->io->note(\sprintf('Upload media "%s" (%s)', $file->getFilename(), $file->getRealPath()));
            }

            $mediaFile = $document ? $document->getSource()[$this->options->fileField] ?? null : null;
            $data = \array_merge($data, [
                $this->options->fileField => $this->urlToAssetArray($file, $mediaFile),
            ]);
        }

        $data = \array_merge($data, [
            $this->options->folderField => $folder,
            $this->options->pathField => $path,
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
        if (!$this->options->dryRun) {
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

        if (!$this->options->forceExtract
            && null !== $mediaFile
            && $mediaFile[EmsFields::CONTENT_FILE_HASH_FIELD] === $hash
            && !empty($mediaFile[EmsFields::CONTENT_FILE_CONTENT])
        ) {
            return $mediaFile;
        }

        $assetArray = [
            EmsFields::CONTENT_FILE_HASH_FIELD => $hash,
            EmsFields::CONTENT_FILE_NAME_FIELD => $filename,
            EmsFields::CONTENT_MIME_TYPE_FIELD => $mimeType,
            EmsFields::CONTENT_FILE_SIZE_FIELD => $file->getSize() ?: null,
        ];
        if (null === $this->tikaHelper) {
            return $assetArray;
        }

        if ($file->getSize() > $this->options->maxFileSizeExtract) {
            $this->io->note(\sprintf('File "%s" to big for extraction (%d bytes)', $file->getFilename(), $file->getSize()));

            return $assetArray;
        }

        if ($this->io->isVerbose()) {
            $this->io->note('Tika extracting');
        }

        $mimeType = $this->mimeTypes->guessMimeType($file->getRealPath());
        $promise = $this->tikaHelper->extract($stream, $mimeType);
        $promise->startText();
        $promise->startMeta();
        try {
            $extractedContent = \mb_substr($promise->getText(), 0, $this->options->maxContentSize, 'UTF-8');
            $extractedContent = Text::superTrim($extractedContent);

            $assetArray[EmsFields::CONTENT_FILE_CONTENT] = $extractedContent;
            $meta = $promise->getMeta();
            $createdDate = $meta->getCreated();
            $assetArray[EmsFields::CONTENT_FILE_DATE] = $createdDate?->format(\DATE_ATOM);
            $assetArray[EmsFields::CONTENT_FILE_AUTHOR] = $meta->getCreator();
            $assetArray[EmsFields::CONTENT_FILE_TITLE] = $meta->getTitle();
            $assetArray[EmsFields::CONTENT_FILE_LANGUAGE] = $meta->getLocale();
        } catch (\Throwable $e) {
            $this->io->warning($e->getMessage());
        }

        return $assetArray;
    }

    public function loadMetadata(string $metadataFile): void
    {
        $metadataFilePath = $this->options->hashMetaDataFile ? $this->getFileByHash($metadataFile) : $metadataFile;

        $rows = $this->fileReader->getData($metadataFilePath);
        $header = \array_map('trim', $rows[0] ?? []);
        $this->metadatas = [];
        foreach ($rows as $rowIndex => $value) {
            if (0 === $rowIndex) {
                continue;
            }
            $row = [];
            foreach ($value as $key => $cell) {
                $row[$header[$key] ?? $key] = $cell;
            }

            $filename = $this->expressionService->evaluateToString($this->options->locateRowExpression, [
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
        return $this->metadatas[$path] ?? [];
    }

    private function getFolderPath(): string
    {
        if (!$this->options->hashFolder) {
            return $this->options->folder;
        }

        $folderZip = $this->getFileByHash($this->options->folder);
        $zip = new \ZipArchive();

        if (true !== $open = $zip->open($folderZip)) {
            throw new \RuntimeException(\sprintf('Failed opening zip %s (ZipArchive %s)', $folderZip, $open));
        }

        if (!$tempZipDir = \tempnam(\sys_get_temp_dir(), 'ems_cli_')) {
            throw new \RuntimeException(\sprintf('Failed creating temp file in "%s"', \sys_get_temp_dir()));
        }

        $this->filesystem->remove($tempZipDir);
        $this->filesystem->mkdir($tempZipDir);

        $zip->extractTo($tempZipDir);
        $this->cleanPaths[] = $tempZipDir;

        return $tempZipDir;
    }

    private function getFileByHash(string $hash): string
    {
        if (!$this->coreApi->file()->headHash($hash)) {
            throw new \RuntimeException(\sprintf('File with hash "%s" not found', $hash));
        }

        $path = $this->coreApi->file()->downloadFile($hash);
        $this->cleanPaths[] = $path;

        return $path;
    }
}
