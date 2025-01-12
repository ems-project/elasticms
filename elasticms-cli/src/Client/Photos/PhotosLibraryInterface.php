<?php

declare(strict_types=1);

namespace App\CLI\Client\Photos;

use Symfony\Component\Finder\SplFileInfo;

interface PhotosLibraryInterface
{
    public function photosCount(): int;

    /**
     * @return iterable<Photo>
     */
    public function getPhotos(): iterable;

    public function getPreviewFile(Photo $photo): ?SplFileInfo;

    public function getOriginalFile(Photo $photo): ?SplFileInfo;
}
