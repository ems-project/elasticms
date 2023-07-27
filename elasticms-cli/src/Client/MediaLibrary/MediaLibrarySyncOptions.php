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
        public readonly bool $dryRun,
        public readonly bool $onlyMissingFile,
        public readonly bool $hashFolder,
        public readonly bool $hashMetaDataFile,
        public readonly int $maxContentSize = 5120
    ) {
    }
}
