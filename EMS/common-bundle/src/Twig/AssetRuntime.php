<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\Standard\Image;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Helper\Text\Encoder;
use EMS\CommonBundle\Storage\NotSavedException;
use EMS\CommonBundle\Storage\Processor\Config;
use EMS\CommonBundle\Storage\Processor\Processor;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\Standard\Json;
use GuzzleHttp\Psr7\MimeType;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AssetRuntime
{
    private readonly Filesystem $filesystem;

    public function __construct(private readonly StorageManager $storageManager, private readonly LoggerInterface $logger, private readonly UrlGeneratorInterface $urlGenerator, private readonly Processor $processor, private readonly string $cacheDir)
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @return array<string, SplFileInfo>
     */
    public function unzip(string $hash, string $saveDir, bool $mergeContent = false): array
    {
        try {
            $checkFilename = $saveDir.\DIRECTORY_SEPARATOR.$this->storageManager->computeStringHash($saveDir);
            $checkHash = \file_exists($checkFilename) ? \file_get_contents($checkFilename) : false;

            if ($checkHash !== $hash) {
                if (!$mergeContent && $this->filesystem->exists($saveDir)) {
                    $this->filesystem->remove($saveDir);
                }

                $this::extract($this->storageManager->getStream($hash), $saveDir);
                \file_put_contents($checkFilename, $hash);
            }

            $excludeCheckFile = fn (SplFileInfo $f) => $f->getPathname() !== $checkFilename;

            return \iterator_to_array(Finder::create()->in($saveDir)->files()->filter($excludeCheckFile)->getIterator());
        } catch (\Exception $e) {
            $this->logger->error('ems_zip failed : {error}', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        return [];
    }

    public function temporaryFile(string $hash): ?string
    {
        if (!$this->storageManager->head($hash)) {
            return null;
        }

        return self::streamToTempFile($this->storageManager->getStream($hash));
    }

    private static function streamToTempFile(StreamInterface $stream): string
    {
        $path = \tempnam(\sys_get_temp_dir(), 'emsch');
        if (!$path) {
            throw new \RuntimeException(\sprintf('Could not create temp file in %s', \sys_get_temp_dir()));
        }

        if ($stream->isSeekable() && $stream->tell() > 0) {
            $stream->rewind();
        }

        $handle = \fopen($path, 'w');
        if (false === $handle) {
            throw new \RuntimeException(\sprintf('Could not open temp file %s', $path));
        }
        while (!$stream->eof()) {
            \fwrite($handle, $stream->read(Processor::BUFFER_SIZE));
        }
        \fclose($handle);
        $stream->close();

        return $path;
    }

    public static function extract(StreamInterface $stream, string $destination): bool
    {
        $path = self::streamToTempFile($stream);

        $zip = new \ZipArchive();
        if (true !== $open = $zip->open($path)) {
            throw new \RuntimeException(\sprintf('Failed opening zip %s (ZipArchive %s)', $path, $open));
        }

        if (!$zip->extractTo($destination)) {
            throw new \RuntimeException(\sprintf('Extracting of zip file failed (%s)', $destination));
        }

        $zip->close();

        return true;
    }

    /**
     * @param array<string, mixed> $fileField
     * @param array<string, mixed> $assetConfig
     */
    public function assetPath(array $fileField, array $assetConfig = [], string $route = 'ems_asset', string $fileHashField = EmsFields::CONTENT_FILE_HASH_FIELD, string $filenameField = EmsFields::CONTENT_FILE_NAME_FIELD, string $mimeTypeField = EmsFields::CONTENT_MIME_TYPE_FIELD, int $referenceType = UrlGeneratorInterface::RELATIVE_PATH): string
    {
        $config = $assetConfig;

        $hash = $fileField[EmsFields::CONTENT_FILE_HASH_FIELD_] ?? $fileField[$fileHashField] ?? 'processor';
        $filename = $fileField[EmsFields::CONTENT_FILE_NAME_FIELD_] ?? $fileField[$filenameField] ?? 'asset.bin';
        $mimeType = $config[EmsFields::ASSET_CONFIG_MIME_TYPE] ?? $fileField[EmsFields::CONTENT_MIME_TYPE_FIELD_] ?? $fileField[$mimeTypeField] ?? MimeType::fromFilename($filename) ?? 'application/octet-stream';
        $referenceType = $assetConfig[EmsFields::ASSET_CONFIG_URL_TYPE] ?? $referenceType;

        $mimeType = $this->processor->overwriteMimeType($mimeType, $config);
        $filename = $this->fixFileExtension($filename, $mimeType);
        $config[EmsFields::ASSET_CONFIG_MIME_TYPE] = $mimeType;

        try {
            $hashConfig = $this->storageManager->saveConfig($config);
        } catch (NotSavedException $e) {
            $hashConfig = $e->getHash();
        }

        if (!($config[EmsFields::ASSET_CONFIG_GET_FILE_PATH] ?? false)) {
            $basename = (new Encoder())->webalizeForUsers(\basename($filename));

            return $this->urlGenerator->generate($route, [
                'hash_config' => $hashConfig,
                'filename' => $basename,
                'hash' => $hash ?? $hashConfig,
            ], $referenceType);
        }

        $configObj = new Config($this->storageManager, $hash, $hashConfig, $config);
        $filesystem = new Filesystem();
        $filesystem->mkdir($this->cacheDir.DIRECTORY_SEPARATOR.'ems_asset_path'.DIRECTORY_SEPARATOR.$hashConfig);
        $cacheFilename = $this->cacheDir.DIRECTORY_SEPARATOR.'ems_asset_path'.DIRECTORY_SEPARATOR.$hashConfig.DIRECTORY_SEPARATOR.$hash;

        if (!$filesystem->exists($cacheFilename)) {
            try {
                $stream = $this->processor->getStream($configObj, $filename);
                \file_put_contents($cacheFilename, $stream->getContents());
            } catch (\Throwable $e) {
                $this->logger->error('Generate the {cacheFilename} failed : {error}', ['hash' => $hash, 'cacheFilename' => $cacheFilename, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }
        }

        return $cacheFilename;
    }

    public function assetAverageColor(string $hash): string
    {
        try {
            $config = $this->processor->configFactory($hash, [
                EmsFields::ASSET_CONFIG_TYPE => EmsFields::ASSET_CONFIG_TYPE_IMAGE,
                EmsFields::ASSET_CONFIG_RESIZE => 'free',
                EmsFields::ASSET_CONFIG_WIDTH => 1,
                EmsFields::ASSET_CONFIG_HEIGHT => 1,
                EmsFields::ASSET_CONFIG_QUALITY => 80,
                EmsFields::ASSET_CONFIG_MIME_TYPE => 'image/jpeg',
            ]);
            $stream = $this->processor->getStream($config, 'one-pixel.jpg');

            $image = \imagecreatefromstring($stream->getContents());
            if (false === $image) {
                throw new \RuntimeException('Unexpected imagecreatefromstring error');
            }
            $index = \imagecolorat($image, 0, 0);
            if (false === $index) {
                throw new \RuntimeException('Unexpected imagecolorat error');
            }
            $rgb = \imagecolorsforindex($image, $index);
            $red = \round(\round(($rgb['red'] ?? 255) / 0x33) * 0x33);
            $green = \round(\round(($rgb['green'] ?? 255) / 0x33) * 0x33);
            $blue = \round(\round(($rgb['blue'] ?? 255) / 0x33) * 0x33);

            return \sprintf('#%02X%02X%02X', $red, $green, $blue);
        } catch (\Throwable) {
            return '#FFFFFF';
        }
    }

    /**
     * @return array<mixed>
     */
    public function jsonFromFile(string $hash): array
    {
        return Json::decode($this->storageManager->getContents($hash));
    }

    /**
     * @return array<string, int|string>|null
     */
    public function imageInfo(string $hash): ?array
    {
        $tempFile = $this->temporaryFile($hash);

        if (null === $tempFile) {
            return null;
        }

        try {
            $imageSize = Image::imageSize($tempFile);
        } catch (\RuntimeException) {
            return null;
        }

        $imageInfo = [
            'width' => $imageSize[0],
            'height' => $imageSize[1],
            'mimeType' => $imageSize['mime'],
            'extension' => \explode('/', (string) $imageSize['mime'])[1],
        ];

        try {
            $imageResolution = Image::imageResolution($tempFile);
        } catch (\RuntimeException) {
            return $imageInfo;
        }

        $imageInfo['widthResolution'] = $imageResolution[0];
        $imageInfo['heightResolution'] = $imageResolution[1];

        return $imageInfo;
    }

    public function hash(string $input, ?string $hashAlgo = null, bool $binary = false): string
    {
        return $this->storageManager->computeStringHash($input, $hashAlgo, $binary);
    }

    private function fixFileExtension(string $filename, string $mimeType): string
    {
        static $mimetypes = [
            'video/3gpp' => '3gp',
            'application/x-7z-compressed' => '7z',
            'audio/x-aac' => 'aac',
            'audio/x-aiff' => 'aif',
            'video/x-ms-asf' => 'asf',
            'application/atom+xml' => 'atom',
            'video/x-msvideo' => 'avi',
            'image/bmp' => 'bmp',
            'application/x-bzip2' => 'bz2',
            'application/pkix-cert' => 'cer',
            'application/pkix-crl' => 'crl',
            'application/x-x509-ca-cert' => 'crt',
            'text/css' => 'css',
            'text/csv' => 'csv',
            'application/cu-seeme' => 'cu',
            'application/x-debian-package' => 'deb',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/x-dvi' => 'dvi',
            'application/vnd.ms-fontobject' => 'eot',
            'application/epub+zip' => 'epub',
            'text/x-setext' => 'etx',
            'audio/flac' => 'flac',
            'video/x-flv' => 'flv',
            'image/gif' => 'gif',
            'application/gzip' => 'gz',
            'text/html' => 'html',
            'image/x-icon' => 'ico',
            'text/calendar' => 'ics',
            'application/x-iso9660-image' => 'iso',
            'application/java-archive' => 'jar',
            'image/jpeg' => 'jpeg',
            'text/javascript' => 'js',
            'application/json' => 'json',
            'application/x-latex' => 'latex',
            'audio/midi' => 'midi',
            'video/quicktime' => 'mov',
            'video/x-matroska' => 'mkv',
            'audio/mpeg' => 'mp3',
            'video/mp4' => 'mp4',
            'audio/mp4' => 'mp4a',
            'video/mpeg' => 'mpeg',
            'audio/ogg' => 'ogg',
            'video/ogg' => 'ogv',
            'application/ogg' => 'ogx',
            'image/x-portable-bitmap' => 'pbm',
            'application/pdf' => 'pdf',
            'image/x-portable-graymap' => 'pgm',
            'image/png' => 'png',
            'image/x-portable-anymap' => 'pnm',
            'image/x-portable-pixmap' => 'ppm',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-rar-compressed' => 'rar',
            'image/x-cmu-raster' => 'ras',
            'application/rss+xml' => 'rss',
            'application/rtf' => 'rtf',
            'text/sgml' => 'sgml',
            'image/svg+xml' => 'svg',
            'application/x-shockwave-flash' => 'swf',
            'application/x-tar' => 'tar',
            'image/tiff' => 'tiff',
            'application/x-bittorrent' => 'torrent',
            'application/x-font-ttf' => 'ttf',
            'text/plain' => 'txt',
            'audio/x-wav' => 'wav',
            'video/webm' => 'webm',
            'image/webp' => 'webp',
            'audio/x-ms-wma' => 'wma',
            'video/x-ms-wmv' => 'wmv',
            'application/x-font-woff' => 'woff',
            'application/wsdl+xml' => 'wsdl',
            'image/x-xbitmap' => 'xbm',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/xml' => 'xml',
            'image/x-xpixmap' => 'xpm',
            'image/x-xwindowdump' => 'xwd',
            'text/yaml' => 'yml',
            'application/zip' => 'zip',
        ];

        if (!isset($mimetypes[$mimeType])) {
            return $filename;
        }

        if (MimeType::fromFilename($filename) === $mimeType) {
            return $filename;
        }

        return \implode('.', [\pathinfo($filename, PATHINFO_FILENAME), $mimetypes[$mimeType]]);
    }
}
