<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Standard;

use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

final class Image
{
    public static function imageCreateFromString(string $resource): \GdImage
    {
        $image = \imagecreatefromstring($resource);

        if (false === $image) {
            throw new \RuntimeException('Unexpected false image');
        }

        return $image;
    }

    /**
     * @return array<int>
     */
    public static function imageResolution(string $imageFile): array
    {
        $resource = self::imageCreateFromFilename($imageFile);
        $imageResolution = \imageresolution($resource);

        if (!\is_array($imageResolution)) {
            throw new \RuntimeException('Unexpected false resolution');
        }

        return $imageResolution;
    }

    /**
     * @return array<int|string, mixed>
     */
    public static function imageSize(string $filePath): array
    {
        $imageSize = \getimagesize($filePath);

        if (false === $imageSize) {
            throw new \RuntimeException('Unexpected false image size');
        }

        return $imageSize;
    }

    public static function imageCreateFromFilename(string $filename): \GdImage
    {
        $symfonyFile = new SymfonyFile($filename, false);
        switch ($symfonyFile->guessExtension()) {
            case 'jpeg':
            case 'jpg':
                $image = \imagecreatefromjpeg($filename);
                break;
            case 'png':
                $image = \imagecreatefrompng($filename);
                break;
            case 'gif':
                $image = \imagecreatefromgif($filename);
                break;
            default:
                $contents = \file_get_contents($filename);
                if (false === $contents) {
                    throw new \RuntimeException('Unexpected missing asset\'s temporary file');
                }
                $image = \imagecreatefromstring($contents);
        }

        if (false === $image) {
            throw new \RuntimeException('Unexpected false image');
        }

        return $image;
    }
}
