<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Processor;

use EMS\CommonBundle\Common\Standard\Type;
use EMS\CommonBundle\Helper\EmsFields;
use Psr\Log\LoggerInterface;

class Image
{
    private ?string $watermark = null;

    public function __construct(private readonly Config $config, private readonly ?LoggerInterface $logger = null)
    {
    }

    public function setWatermark(string $watermark): void
    {
        $this->watermark = $watermark;
    }

    public function generate(string $filename, string $cacheFilename = null): string
    {
        $length = \filesize($filename);
        if (false === $length) {
            throw new \RuntimeException('Could not read file');
        }

        $handle = \fopen($filename, 'r');
        if (false === $handle) {
            throw new \RuntimeException('Could not open file');
        }
        $contents = \fread($handle, $length);
        \fclose($handle);

        if (false === $contents) {
            throw new \RuntimeException('Could not read file');
        }
        if (!$image = @\imagecreatefromstring($contents)) {
            throw new \InvalidArgumentException('could not make image');
        }

        $image = $this->autorotate($filename, $image);
        $this->applyFlips($image, $this->config->getFlipHorizontal(), $this->config->getFlipVertical());
        $image = $this->rotate($image, $this->config->getRotate());

        $rotatedWidth = Type::integer(\imagesx($image));
        $rotatedHeight = Type::integer(\imagesy($image));

        [$width, $height] = $this->getWidthHeight($rotatedWidth, $rotatedHeight);

        if (null !== $this->config->getResize()) {
            $image = $this->applyResizeAndBackground($image, $width, $height, $rotatedWidth, $rotatedHeight);
        } elseif (null !== $this->config->getBackground()) {
            $image = $this->applyBackground($image, $width, $height);
        }

        if ($this->config->getRadius() > 0) {
            $image = $this->applyCorner($image, $width, $height);
        }

        $image = $this->applyWatermark($image, $width, $height);

        if (null !== $cacheFilename) {
            if (!\file_exists(\dirname($cacheFilename))) {
                \mkdir(\dirname($cacheFilename), 0777, true);
            }
            $path = $cacheFilename;
        } else {
            $path = \tempnam(\sys_get_temp_dir(), 'ems_image');
            if (false === $path) {
                throw new \RuntimeException('Could not create file with unique name.');
            }
        }

        if (EmsFields::ASSET_CONFIG_WEBP_IMAGE_FORMAT === $this->config->getImageFormat()) {
            \imagewebp($image, $path, $this->config->getQuality());
        } elseif (EmsFields::ASSET_CONFIG_BMP_IMAGE_FORMAT === $this->config->getImageFormat()) {
            \imagebmp($image, $path);
        } elseif (EmsFields::ASSET_CONFIG_GIF_IMAGE_FORMAT === $this->config->getImageFormat()) {
            \imagegif($image, $path);
        } elseif (EmsFields::ASSET_CONFIG_JPEG_IMAGE_FORMAT === $this->config->getImageFormat() || (null === $this->config->getImageFormat() && $this->config->getQuality() > 0)) {
            \imagejpeg($image, $path, $this->config->getQuality());
        } else {
            \imagepng($image, $path);
        }
        \imagedestroy($image);

        return $path;
    }

    /**
     * @return array<int>
     */
    private function getWidthHeight(int $originalWidth, int $originalHeight): array
    {
        $width = $this->config->getWidth();
        $height = $this->config->getHeight();

        if ('ratio' !== $this->config->getResize()) {
            $width = (0 === $width ? $originalWidth : $width);
            $height = (0 === $height ? $originalHeight : $height);

            return [$width, $height];
        }

        $ratio = $originalWidth / $originalHeight;

        if (0 === $width && 0 === $height) {
            // unable to calculate ratio, silently return original size (backward compatibility)
            return [\intval($originalWidth), \intval($originalHeight)];
        }

        if (0 === $width || 0 === $height) {
            if (0 === $height) {
                // recalculate height
                $height = \ceil((float) $width / $ratio);
            } else {
                // recalculate width
                $width = \ceil($ratio * (float) $height);
            }
        } else {
            if (($originalHeight / $height) > ($originalWidth / $width)) {
                $width = \ceil($ratio * (float) $height);
            } else {
                $height = \ceil((float) $width / $ratio);
            }
        }

        return [\intval($width), \intval($height)];
    }

    private function fillBackgroundColor(\GdImage $temp): void
    {
        $solidColour = $this->getBackgroundColor($temp);
        \imagesavealpha($temp, true);
        \imagefill($temp, 0, 0, $solidColour);
    }

    private function applyResizeAndBackground(\GdImage $image, int $width, int $height, int $originalWidth, int $originalHeight): \GdImage
    {
        $temp = $this->imageCreate($width, $height);

        $this->fillBackgroundColor($temp);

        $resize = $this->config->getResize();
        $gravity = $this->config->getGravity();

        if ('fillArea' == $resize) {
            if (($originalHeight / $height) < ($originalWidth / $width)) {
                $cal_width = \intval($originalHeight * $width / $height);
                if (false !== \stripos($gravity, 'west')) {
                    $this->imageCopyResized($temp, $image, 0, 0, 0, 0, $width, $height, $cal_width, $originalHeight);
                } elseif (false !== \stripos($gravity, 'east')) {
                    $this->imageCopyResized($temp, $image, 0, 0, $originalWidth - $cal_width, 0, $width, $height, $cal_width, $originalHeight);
                } else {
                    $this->imageCopyResized($temp, $image, 0, 0, \intval(($originalWidth - $cal_width) / 2), 0, $width, $height, $cal_width, $originalHeight);
                }
            } else {
                $cal_height = \intval($originalWidth / $width * $height);
                if (false !== \stripos($gravity, 'north')) {
                    $this->imageCopyResized($temp, $image, 0, 0, 0, 0, $width, $height, $originalWidth, $cal_height);
                } elseif (false !== \stripos($gravity, 'south')) {
                    $this->imageCopyResized($temp, $image, 0, 0, 0, $originalHeight - $cal_height, $width, $height, $originalWidth, $cal_height);
                } else {
                    $this->imageCopyResized($temp, $image, 0, 0, 0, \intval(($originalHeight - $cal_height) / 2), $width, $height, $originalWidth, $cal_height);
                }
            }
        } elseif ('fill' == $resize) {
            if (($originalHeight / $height) < ($originalWidth / $width)) {
                $thumb_height = \intval($width * $originalHeight / $originalWidth);
                $this->imageCopyResized($temp, $image, 0, \intval(($height - $thumb_height) / 2), 0, 0, $width, $thumb_height, $originalWidth, $originalHeight);
            } else {
                $thumb_width = \intval(($originalWidth * $height) / $originalHeight);
                $this->imageCopyResized($temp, $image, \intval(($width - $thumb_width) / 2), 0, 0, 0, $thumb_width, $height, $originalWidth, $originalHeight);
            }
        } else {
            $this->imageCopyResized($temp, $image, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);
        }

        return $temp;
    }

    private function applyBackground(\GdImage $image, int $width, int $height): \GdImage
    {
        $temp = $this->imageCreate($width, $height);

        $this->fillBackgroundColor($temp);

        $this->imageCopyResized($temp, $image, 0, 0, 0, 0, $width, $height, $width, $height);

        return $temp;
    }

    private function applyCorner(\GdImage $image, int $width, int $height): \GdImage
    {
        $radius = $this->config->getRadius();
        $color = $this->config->getBorderColor() ?? $this->config->getBackground();

        $cornerImage = $this->imageCreate($radius, $radius);
        $clearColor = \imagecolorallocate($cornerImage, 0, 0, 0);
        if (false === $clearColor) {
            throw new \RuntimeException('Unexpected false imagecolorallocate');
        }
        $solidColor = \imagecolorallocate($cornerImage, (int) \hexdec(\substr($color, 1, 2)), (int) \hexdec(\substr($color, 3, 2)), (int) \hexdec(\substr($color, 5, 2)));
        if (false === $solidColor) {
            throw new \RuntimeException('Unexpected false imagecolorallocate');
        }

        \imagecolortransparent($cornerImage, $clearColor);
        \imagefill($cornerImage, 0, 0, $solidColor);
        \imagefilledellipse($cornerImage, $radius, $radius, $radius * 2, $radius * 2, $clearColor);

        $radiusGeometry = $this->config->getRadiusGeometry();

        // render the top-left, bottom-left, bottom-right, top-right corners by rotating and copying the mask
        if (false !== \in_array('topleft', $radiusGeometry)) {
            \imagecopymerge($image, $cornerImage, 0, 0, 0, 0, $radius, $radius, 100);
        }
        $cornerImage = \imagerotate($cornerImage, 90, 0);
        if (false === $cornerImage) {
            throw new \RuntimeException('Unexpected false image');
        }

        if (false !== \in_array('bottomleft', $radiusGeometry)) {
            \imagecopymerge($image, $cornerImage, 0, $height - $radius, 0, 0, $radius, $radius, 100);
        }
        $cornerImage = \imagerotate($cornerImage, 90, 0);
        if (false === $cornerImage) {
            throw new \RuntimeException('Unexpected false image');
        }

        if (false !== \in_array('bottomright', $radiusGeometry)) {
            \imagecopymerge($image, $cornerImage, $width - $radius, $height - $radius, 0, 0, $radius, $radius, 100);
        }
        $cornerImage = \imagerotate($cornerImage, 90, 0);
        if (false === $cornerImage) {
            throw new \RuntimeException('Unexpected false image');
        }

        if (false !== \in_array('topright', $radiusGeometry)) {
            \imagecopymerge($image, $cornerImage, $width - $radius, 0, 0, 0, $radius, $radius, 100);
        }

        $transparentColor = \imagecolorallocate($image, (int) \hexdec(\substr($color, 1, 2)), (int) \hexdec(\substr($color, 3, 2)), (int) \hexdec(\substr($color, 5, 2)));
        if (false === $transparentColor) {
            throw new \RuntimeException('Unexpected false imagecolorallocate');
        }
        \imagecolortransparent($image, $transparentColor);

        return $image;
    }

    private function applyWatermark(\GdImage $image, int $width, int $height): \GdImage
    {
        if (null === $this->watermark) {
            return $image;
        }
        $stamp = \imagecreatefrompng($this->watermark);
        if (false === $stamp) {
            throw new \RuntimeException('Could not convert watermark to image');
        }
        $sx = Type::integer(\imagesx($stamp));
        $sy = Type::integer(\imagesy($stamp));
        \imagecopy($image, $stamp, (int) ($width - $sx) / 2, (int) ($height - $sy) / 2, 0, 0, $sx, $sy);

        return $image;
    }

    private function applyFlips(\GdImage $image, bool $flipHorizontal, bool $flipVertical): void
    {
        if ($flipHorizontal && $flipVertical) {
            \imageflip($image, IMG_FLIP_BOTH);
        } elseif ($flipHorizontal) {
            \imageflip($image, IMG_FLIP_HORIZONTAL);
        } elseif ($flipVertical) {
            \imageflip($image, IMG_FLIP_VERTICAL);
        }
    }

    private function rotate(\GdImage $image, float $angle): \GdImage
    {
        if (0 == $angle) {
            return $image;
        }

        $rotated = \imagerotate($image, $angle, $this->getBackgroundColor($image));
        if (false === $rotated) {
            throw new \RuntimeException('Could not rotate the image');
        }
        \imagedestroy($image);

        return $rotated;
    }

    private function getBackgroundColor(\GdImage $temp): int
    {
        $background = $this->config->getBackground();
        $solidColour = \imagecolorallocatealpha(
            $temp,
            (int) \hexdec(\substr($background, 1, 2)),
            (int) \hexdec(\substr($background, 3, 2)),
            (int) \hexdec(\substr($background, 5, 2)),
            \intval(\hexdec(\substr($background, 7, 2)) / 2)
        );
        if (false === $solidColour) {
            throw new \RuntimeException('Unexpected false imagecolorallocatealpha');
        }

        return $solidColour;
    }

    /**
     * The 8 EXIF orientation values are numbered 1 to 8.
     * 1 = 0 degrees: the correct orientation, no adjustment is required.
     * 2 = 0 degrees, mirrored: image has been flipped back-to-front.
     * 3 = 180 degrees: image is upside down.
     * 4 = 180 degrees, mirrored: image has been flipped back-to-front and is upside down.
     * 5 = 270 degrees anticlockwise: image has been flipped back-to-front and is on its side.
     * 6 = 270 degrees anticlockwise, mirrored: image is on its side.
     * 7 = 90 degrees anticlockwise: image has been flipped back-to-front and is on its far side.
     * 8 = 90 degrees anticlockwise, mirrored: image is on its far side.
     * ref: https://sirv.com/help/articles/rotate-photos-to-be-upright/.
     */
    private function autorotate(string $filename, \GdImage $image): \GdImage
    {
        if (!$this->config->getAutoRotate()) {
            return $image;
        }

        try {
            $metadata = \exif_read_data($filename);
            if (false === $metadata) {
                return $image;
            }
            $angle = 0;
            $mirrored = false;
            switch ($metadata['Orientation'] ?? 0) {
                case 2:
                    $mirrored = true;
                    break;
                case 3:
                    $angle = 180;
                    break;
                case 4:
                    $angle = 180;
                    $mirrored = true;
                    break;
                case 5:
                    $angle = 270;
                    break;
                case 6:
                    $angle = 270;
                    $mirrored = true;
                    break;
                case 7:
                    $angle = 90;
                    break;
                case 8:
                    $angle = 90;
                    $mirrored = true;
                    break;
            }
            $image = $this->rotate($image, $angle);
            $this->applyFlips($image, $mirrored, false);
        } catch (\Throwable $e) {
            if (null !== $this->logger) {
                $this->logger->warning(\sprintf('Not able to autorotate a file due to: %s', $e->getMessage()));
            }
        }

        return $image;
    }

    private function imageCreate(int $width, int $height): \GdImage
    {
        if (!\function_exists('imagecreatetruecolor') || false === ($image = \imagecreatetruecolor($width, $height))) {
            $image = \imagecreate($width, $height);
        }
        if (false === $image) {
            throw new \RuntimeException('Unexpected false imagecreate');
        }

        return $image;
    }

    private function imageCopyResized(\GdImage $dstImage, \GdImage $srcImage, int $dstX, int $dstY, int $srcX, int $srcY, int $dstWidth, int $dstHeight, int $srcWidth, int $srcHeight): void
    {
        if (\function_exists('imagecreatetruecolor') && \function_exists('imagecopyresampled')) {
            $resizeFunction = 'imagecopyresampled';
        } else {
            $resizeFunction = 'imagecopyresized';
        }

        if (false === \call_user_func($resizeFunction, $dstImage, $srcImage, $dstX, $dstY, $srcX, $srcY, $dstWidth, $dstHeight, $srcWidth, $srcHeight)) {
            throw new \RuntimeException('Unexpected error while resizing image');
        }
    }
}
