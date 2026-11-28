<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Standard;

use EMS\Helpers\Standard\Type;
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
                    throw new \RuntimeException("Unexpected missing asset's temporary file");
                }
                $image = \imagecreatefromstring($contents);
        }

        if (false === $image) {
            throw new \RuntimeException('Unexpected false image');
        }

        return $image;
    }

    /**
     * @return array<string, mixed>
     */
    public static function imageExifInfo(string $tempFile): array
    {
        $exif = Type::array(@\exif_read_data($tempFile));
        $exifData = [];
        if (isset($exif['XResolution'], $exif['YResolution'])) {
            $x = $exif['XResolution'];
            $y = $exif['YResolution'];

            $exifData['widthResolution'] = \is_string($x) && \str_contains($x, '/')
                ? ((int) \explode('/', $x)[0] / (int) \explode('/', $x)[1])
                : (int) $x;

            $exifData['heightResolution'] = \is_string($y) && \str_contains($y, '/')
                ? ((int) \explode('/', $y)[0] / (int) \explode('/', $y)[1])
                : (int) $y;
        }

        return \array_merge($exifData, $exif);
    }
}
