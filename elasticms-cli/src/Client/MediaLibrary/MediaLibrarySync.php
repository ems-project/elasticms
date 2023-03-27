<?php

declare(strict_types=1);

namespace App\CLI\Client\MediaLibrary;

use Elastica\Query\Terms;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiExceptionInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;
use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Search\Search;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Finder\Finder;

final class MediaLibrarySync
{
    /** @var mixed[] */
    private array $metadatas = [];
    private DataInterface $contentTypeApi;
    private string $defaultAlias;

    public function __construct(
        private readonly string $folder,
        private readonly string $contentType,
        private readonly string $folderField,
        private readonly string $pathField,
        private readonly string $fileField,
        private readonly SymfonyStyle $io,
        private readonly bool $dryRun,
        private readonly CoreApiInterface $coreApi,
        private readonly FileReaderInterface $fileReader)
    {
        $this->contentTypeApi = $this->coreApi->data($this->contentType);
        $this->defaultAlias = $this->coreApi->meta()->getDefaultContentTypeEnvironmentAlias($this->contentType);
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
                $this->uploadMediaFile($file, DIRECTORY_SEPARATOR.$file->getRelativePathname());
            } catch (\Throwable $e) {
                $this->io->error(\sprintf('Upload failed for "%s" (%s)', $file->getRealPath(), $e->getMessage()));
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->io->newLine();

        return $this;
    }

    private function uploadMediaFile(\SplFileInfo $file, string $path): void
    {
        $exploded = \explode(DIRECTORY_SEPARATOR, $path);
        $metadata = $this->getMetadata($path);
        $ouuid = null;
        while (\count($exploded) > 1) {
            $path = \implode(DIRECTORY_SEPARATOR, $exploded);
            \array_pop($exploded);
            $folder = \implode(DIRECTORY_SEPARATOR, $exploded).DIRECTORY_SEPARATOR;

            $data = [
                $this->pathField => $path,
                $this->folderField => $folder,
                '_sync_metadata' => $metadata,
            ];
            if (null === $ouuid) {
                $data[$this->fileField] = $this->urlToAssetArray($file);
            }

            $term = new Terms($this->pathField, [$path]);
            $search = new Search([$this->defaultAlias], $term->toArray());
            $search->setContentTypes([$this->contentType]);
            $result = $this->coreApi->search()->search($search);
            $document = null;
            foreach ($result->getDocuments() as $item) {
                $document = $item;
                break;
            }

            if (!$this->dryRun) {
                if (null === $document) {
                    $draft = $this->contentTypeApi->create($data);
                } elseif (empty($metadata) && \is_array($source = $data[$this->fileField] ?? null) && \is_array($target = $document->getSource()[$this->fileField] ?? null) && empty(\array_diff($source, $target)) && $data[$this->folderField] === ($document->getSource()[$folder] ?? null)) {
                    $ouuid ??= $document->getId();
                    continue;
                } else {
                    $draft = $this->contentTypeApi->update($document->getId(), $data);
                }

                if (null === $ouuid) {
                    $ouuid = $this->contentTypeApi->finalize($draft->getRevisionId());
                } else {
                    $this->contentTypeApi->finalize($draft->getRevisionId());
                }
            }
        }
    }

    /**
     * @return array{sha1: string, filename: string, mimetype: string, filesize: int|null }|array{}
     */
    public function urlToAssetArray(\SplFileInfo $file): array
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

        return [
            EmsFields::CONTENT_FILE_HASH_FIELD => $hash,
            EmsFields::CONTENT_FILE_NAME_FIELD => $filename,
            EmsFields::CONTENT_MIME_TYPE_FIELD => $mimeType,
            EmsFields::CONTENT_FILE_SIZE_FIELD => $file->getSize() ? $file->getSize() : null,
        ];
    }

    public function loadMetadata(string $metadataFile, string $locateRowExpression): void
    {
        $expressionLanguage = new ExpressionLanguage();
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

            $filename = $expressionLanguage->evaluate($locateRowExpression, [
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
