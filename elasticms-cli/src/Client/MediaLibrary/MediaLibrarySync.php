<?php

declare(strict_types=1);

namespace App\CLI\Client\MediaLibrary;

use Elastica\Query\BoolQuery;
use Elastica\Query\Terms;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiExceptionInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Search\Search;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

final class MediaLibrarySync
{
    /** @var array<string, string> */
    private array $dataArray;

    /**
     * @param array{content_type: string, folder_field: string, path_field: string, file_field: string} $config
     */
    public function __construct(private readonly string $folder, private readonly array $config, private readonly SymfonyStyle $io, private readonly bool $dryRun, private readonly CoreApiInterface $coreApi, private readonly ?MediaLibraryConfig $configFile, private readonly FileReaderInterface $fileReader)
    {
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

        if (null != $this->configFile) {
            $this->io->section('A Config File is found - Reading data');
            $this->dataArray = $this->fileReader->getData($this->configFile->xlsPath, true);
            $this->io->comment(\sprintf('Loaded data in memory: %d rows', \count($this->dataArray)));
        }

        $progressBar = $this->io->createProgressBar($finder->count());

        foreach ($finder as $file) {
            try {
                $position = \strpos($file->getRealPath(), $this->folder);
                $path = \substr($file->getRealPath(), $position + \strlen($this->folder));
                if (!\str_starts_with($path, '/')) {
                    $path = '/'.$path;
                }
                $this->uploadMediaFile($file, $path);
            } catch (\Throwable $e) {
                $this->io->error(\sprintf('Upload failed for "%s" (%s)', $file->getRealPath(), $e->getMessage()));
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->io->newLine();

        return $this;
    }

    private function uploadMediaFile(\SplFileInfo $file, string $path): string
    {
        $exploded = \explode('/', $path);
        $ouuid = null;
        $defaultAlias = $this->coreApi->meta()->getDefaultContentTypeEnvironmentAlias($this->config['content_type']);
        $contentTypeApi = $this->coreApi->data($this->config['content_type']);
        while (\count($exploded) > 1) {
            $path = \implode('/', $exploded);
            \array_pop($exploded);
            $folder = \implode('/', $exploded).'/';

            $data = [
                $this->config['path_field'] => $path,
                $this->config['folder_field'] => $folder,
            ];
            if (null === $ouuid) {
                $data[$this->config['file_field']] = $this->urlToAssetArray($file);
            }

            $term = new Terms($this->config['path_field'], [$path]);
            $search = new Search([$defaultAlias], $term->toArray());

            if (null != $this->configFile && \str_ends_with($path, $file->getFilename())) {
                if (null != $this->configFile->getFolderColumn() && null != $this->configFile->getFilenameColumn()) {
                    $rows = $this->dataSearch($this->dataArray, $this->configFile->getFilenameColumn()->indexDataColumn, $file->getFilename());
                    $folderPath = \implode('/', $exploded);
                    if (\str_starts_with($folderPath, '/')) {
                        $folderPath = \substr($folderPath, 1);
                    }
                    $row = $this->dataSearch($rows, $this->configFile->getFolderColumn()->indexDataColumn, $folderPath);
                    if (1 == \count($row)) {
                        $row = $row[0];
                        $query = new BoolQuery();
                        $query->addMust(new Terms($this->configFile->getFolderColumn()->field, [$row[$this->configFile->getFolderColumn()->indexDataColumn]]));
                        $query->addMust(new Terms($this->configFile->getFilenameColumn()->field, [$row[$this->configFile->getFilenameColumn()->indexDataColumn]]));
                        $search = new Search([$defaultAlias], $query->toArray());
                        $data = \array_merge($data, $this->getRawDataFromRow($row));
                    } else {
                        $this->io->error(\sprintf('The config is not found "%s" (folder: %s)', $file->getRealPath(), $folderPath));

                        return '';
                    }
                } else {
                    $this->io->error(\sprintf('The config filename or folder is not found'));

                    return '';
                }
            }

            $search->setContentTypes([$this->config['content_type']]);
            $result = $this->coreApi->search()->search($search);
            $document = null;
            foreach ($result->getDocuments() as $item) {
                $document = $item;
                break;
            }

            if (!$this->dryRun) {
                if (null === $document) {
                    $draft = $contentTypeApi->create($data);
                } else {
                    $dSource = [];
                    $dTarget = [];
                    foreach ($data as $key => $value) {
                        if ($key !== $this->config['file_field']) {
                            $dTarget[$key] = $document->getSource()[$key] ?? null;
                            $dSource[$key] = $value;
                        }
                    }

                    if (($dSource === $dTarget) && \is_array($source = $data[$this->config['file_field']] ?? null) && \is_array($target = $document->getSource()[$this->config['file_field']] ?? null) && empty(\array_diff($source, $target)) && $data[$this->config['folder_field']] === ($document->getSource()[$this->config['folder_field']] ?? null)) {
                        $ouuid ??= $document->getId();
                        break;
                    } else {
                        $draft = $contentTypeApi->update($document->getId(), $data);
                    }
                }

                if (null === $ouuid) {
                    $ouuid = $contentTypeApi->finalize($draft->getRevisionId());
                } else {
                    $contentTypeApi->finalize($draft->getRevisionId());
                }
            }
        }

        return \sprintf('ems://file:%s:%s', $this->config['content_type'], $ouuid);
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

    /**
     * @param  array<mixed> $data
     * @return array<mixed>
     */
    private function dataSearch(array $data, int $columnIndex, string $search): array
    {
        $rows = [];
        foreach ($data as &$row) {
            if (isset($row[$columnIndex]) && $search === (string) $row[$columnIndex]) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param array<mixed> $row
     *
     * @return array<mixed>
     */
    private function getRawDataFromRow(array $row): array
    {
        $rawData = [];
        if (null != $this->configFile) {
            $mediaLibraryMapping = $this->configFile->mediaLibraryMapping;

            foreach ($mediaLibraryMapping as $mediaLibraryMap) {
                $value = $row[$mediaLibraryMap->indexDataColumn] ?? null;

                if (null !== $value) {
                    $rawData[$mediaLibraryMap->field] = $value;
                }

                if (null === $value && $mediaLibraryMap->isRequired) {
                    throw new \RuntimeException('Row does not contain media library value in column [%d]', $mediaLibraryMap->indexDataColumn);
                }
            }

            if (0 === \count($rawData)) {
                throw new \RuntimeException('No media library found!');
            }
        }

        return $rawData;
    }
}
