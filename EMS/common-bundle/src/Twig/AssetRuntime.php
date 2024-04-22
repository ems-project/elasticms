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
use EMS\Helpers\File\TempFile;
use EMS\Helpers\Standard\Json;
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
            $this->logger->error('ems_zip failed : %error%', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        return [];
    }

    public function temporaryFile(string $hash): ?string
    {
        if (!$this->storageManager->head($hash)) {
            return null;
        }

        return TempFile::createNamed($hash, $this->cacheDir)
            ->loadFromStream($this->storageManager->getStream($hash))
            ->path;
    }

    public static function extract(StreamInterface $stream, string $destination): bool
    {
        $tempFile = TempFile::create()->loadFromStream($stream);

        $zip = new \ZipArchive();
        if (true !== $open = $zip->open($tempFile->path)) {
            throw new \RuntimeException(\sprintf('Failed opening zip %s (ZipArchive %s)', $tempFile->path, $open));
        }

        if (!$zip->extractTo($destination)) {
            throw new \RuntimeException(\sprintf('Extracting of zip file failed (%s)', $destination));
        }

        $zip->close();
        $tempFile->clean();

        return true;
    }

    /**
     * @param array<string, mixed> $fileField
     * @param array<string, mixed> $assetConfig
     */
    public function assetPath(array $fileField, array $assetConfig = [], string $route = 'ems_asset', string $fileHashField = EmsFields::CONTENT_FILE_HASH_FIELD, string $filenameField = EmsFields::CONTENT_FILE_NAME_FIELD, string $mimeTypeField = EmsFields::CONTENT_MIME_TYPE_FIELD, int $referenceType = UrlGeneratorInterface::RELATIVE_PATH): string
    {
        $config = $assetConfig;

        $hash = Config::extractHash($fileField, $fileHashField);
        $filename = Config::extractFilename($fileField, $config, $filenameField, $mimeTypeField);
        $mimeType = Config::extractMimetype($fileField, $config, $filename, $mimeTypeField);
        $referenceType = Config::extractUrlType($fileField, $referenceType);

        $mimeType = $this->processor->overwriteMimeType($mimeType, $config);
        $filename = Config::fixFileExtension($filename, $mimeType);
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
                'hash' => $hash,
            ], $referenceType);
        }

        $configObj = new Config($this->storageManager, $hash, $hashConfig, $config);

        $tempName = TempFile::createNamed(\implode('-', [$hashConfig, $hash]), $this->cacheDir);
        if (!$tempName->exists()) {
            $stream = $this->processor->getStream($configObj, $filename);
            $tempName->loadFromStream($stream);
        }

        return $tempName->path;
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

    public function getContent(string $hash): string
    {
        return $this->storageManager->getContents($hash);
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
}
