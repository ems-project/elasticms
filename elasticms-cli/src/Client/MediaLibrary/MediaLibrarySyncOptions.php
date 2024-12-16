<?php

declare(strict_types=1);

namespace App\CLI\Client\MediaLibrary;

final class MediaLibrarySyncOptions
{
    public function __construct(
        public readonly string $folder,
        public readonly string $contentType,
        public readonly string $folderField,
        public readonly string $pathField,
        public readonly string $fileField,
        public readonly ?string $metaDataFile,
        public readonly string $locateRowExpression,
        public readonly string $targetFolder,
        public readonly bool $dryRun,
        public readonly bool $onlyMissingFile,
        public readonly bool $onlyMetadataFile,
        public readonly bool $hashFolder,
        public readonly bool $hashMetaDataFile,
        public readonly bool $forceExtract = false,
        public readonly int $maxContentSize = 5120,
        public readonly int $maxFileSizeExtract = (64 * 1024 * 1024),
    ) {
        if (!\str_starts_with($targetFolder, '/')) {
            throw new \RuntimeException('The target-folder options must start with a /');
        }
    }
}
