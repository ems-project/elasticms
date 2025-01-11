<?php

declare(strict_types=1);

namespace App\CLI\Client\MediaLibrary;

final readonly class MediaLibrarySyncOptions
{
    public function __construct(
        public string $folder,
        public string $contentType,
        public string $folderField,
        public string $pathField,
        public string $fileField,
        public ?string $metaDataFile,
        public string $locateRowExpression,
        public string $targetFolder,
        public bool $dryRun,
        public bool $onlyMissingFile,
        public bool $onlyMetadataFile,
        public bool $hashFolder,
        public bool $hashMetaDataFile,
        public bool $forceExtract = false,
        public int $maxContentSize = 5120,
        public int $maxFileSizeExtract = (64 * 1024 * 1024),
    ) {
        if (!\str_starts_with($targetFolder, '/')) {
            throw new \RuntimeException('The target-folder options must start with a /');
        }
    }
}
