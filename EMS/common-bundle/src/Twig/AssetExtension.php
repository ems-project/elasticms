<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\Standard\Image;
use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Helper\Text\Encoder;
use EMS\CommonBundle\Storage\NotSavedException;
use EMS\CommonBundle\Storage\Processor\Config;
use EMS\CommonBundle\Storage\Processor\Processor;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\File\TempFile;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;

class AssetExtension
{
    public function __construct(
        private readonly StorageManager $storageManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Processor $processor,
        private readonly FileReaderInterface $fileReader,
    ) {
    }

    #[AsTwigFilter(name: 'ems_asset_average_color', isSafe: ['html'])]
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
            $red = \round(\round($rgb['red'] / 0x33) * 0x33);
            $green = \round(\round($rgb['green'] / 0x33) * 0x33);
            $blue = \round(\round($rgb['blue'] / 0x33) * 0x33);

            return \sprintf('#%02X%02X%02X', $red, $green, $blue);
        } catch (\Throwable) {
            return '#FFFFFF';
        }
    }

    /**
     * @param array<string, mixed> $fileField
     * @param array<string, mixed> $assetConfig
     */
    #[AsTwigFunction(name: 'ems_asset_path', isSafe: ['html'])]
    public function assetPath(array $fileField, array $assetConfig = [], string $route = 'ems_asset', string $fileHashField = EmsFields::CONTENT_FILE_HASH_FIELD, string $filenameField = EmsFields::CONTENT_FILE_NAME_FIELD, string $mimeTypeField = EmsFields::CONTENT_MIME_TYPE_FIELD, int $referenceType = UrlGeneratorInterface::RELATIVE_PATH): string
    {
        $config = $assetConfig;

        $hash = Config::extractHash($fileField, $fileHashField, (string) ($assetConfig[EmsFields::ASSET_CONFIG_TYPE] ?? 'none'));
        $filename = Config::extractFilename($fileField, $config, $filenameField, $mimeTypeField);
        $mimeType = Config::extractMimetype($fileField, $config, $filename, $mimeTypeField);
        $referenceType = Config::extractUrlType($fileField, $referenceType);

        $mimeType = $this->processor->overwriteMimeType($mimeType, $config);
        $filename = Config::fixFileExtension($filename, $mimeType);
        $config[EmsFields::ASSET_CONFIG_MIME_TYPE] = $mimeType;

        try {
            $hashConfig = $this->storageManager->saveConfig($config);
        } catch (NotSavedException $notSavedException) {
            $hashConfig = $notSavedException->getHash();
        }
        if (!($config[EmsFields::ASSET_CONFIG_GET_FILE_PATH] ?? false)) {
            $basename = new Encoder()->slug(text: \basename($filename), preserveFileExtension: true);

            return $this->urlGenerator->generate($route, [
                'hash_config' => $hashConfig,
                'filename' => $basename,
                'hash' => $hash,
            ], $referenceType);
        }

        $configObj = new Config($this->storageManager, $hash, $hashConfig, $config);
        $tempName = TempFile::create();
        $stream = $this->processor->getStream($configObj, $filename);
        $tempName->loadFromStream($stream);

        return $tempName->path;
    }

    /**
     * @param mixed[] $options
     */
    #[AsTwigFunction(name: 'ems_file_from_archive')]
    public function fileFromArchive(string $hash, string $path, array $options = []): string|TempFile|null
    {
        $extract = Type::bool($options['extract'] ?? true);
        $asTempFile = Type::bool($options['asTempFile'] ?? false);
        try {
            $streamWrapper = $this->storageManager->getStreamFromArchive($hash, $path, $extract);
            $tempFile = TempFile::create();
            $tempFile->loadFromStream($streamWrapper->getStream());
        } catch (NotFoundHttpException $notFoundHttpException) {
            if ($extract) {
                throw $notFoundHttpException;
            }

            return null;
        }

        return $asTempFile ? $tempFile : $tempFile->path;
    }

    /**
     * @param array{
     *     delimiter?: ?string,
     *     encoding?: ?string,
     *     all_sheets?: ?bool,
     * } $options
     *
     * @return mixed[]
     */
    #[AsTwigFunction(name: 'ems_file_reader_data')]
    public function fileReaderGetData(string $hash, array $options = []): array
    {
        $tempFilename = $this->temporaryFile($hash);
        if (null === $tempFilename) {
            throw new \RuntimeException('Unexpected temporary filename');
        }

        return $this->fileReader->getData($tempFilename, $options);
    }

    /**
     * @param array{
     *      mime_type?: ?string,
     *      delimiter?: ?string,
     *      encoding?: ?string,
     *      exclude_rows?: int[],
     *      limit?: ?int,
     *  } $options
     *
     * @return \Generator<mixed>
     */
    #[AsTwigFunction(name: 'ems_file_reader_cells')]
    public function fileReaderReadCells(string $hash, array $options = []): \Generator
    {
        $tempFilename = $this->temporaryFile($hash);
        if (null === $tempFilename) {
            throw new \RuntimeException('Unexpected temporary filename');
        }

        return $this->fileReader->readCells($tempFilename, $options);
    }

    #[AsTwigFunction(name: 'ems_asset_get_content')]
    public function getContent(string $hash): string
    {
        return $this->storageManager->getContents($hash);
    }

    /**
     * @return array<string, array{filename: string, hash: string, type: string, size: int}>
     */
    #[AsTwigFunction(name: 'ems_files_in_archive')]
    public function getFilesInArchive(string $hash): array
    {
        $archive = $this->storageManager->getFilesInArchive($hash);
        $output = [];
        foreach ($archive->iterator() as $file) {
            $output[$file->filename] = $file->jsonSerialize();
        }

        return $output;
    }

    #[AsTwigFilter(name: 'ems_hash')]
    public function hash(string $input, ?string $hashAlgo = null, bool $binary = false): string
    {
        return $this->storageManager->computeStringHash($input, $hashAlgo, $binary);
    }

    #[AsTwigFunction(name: 'ems_asset_head')]
    public function head(string $hash): bool
    {
        return $this->storageManager->head($hash);
    }

    /**
     * @return \Traversable<int, string|true>
     */
    #[AsTwigFunction(name: 'ems_asset_heads')]
    public function heads(string ...$fileHashes): \Traversable
    {
        return $this->storageManager->heads(...$fileHashes);
    }

    /**
     * @return array<string, mixed>|null
     */
    #[AsTwigFunction(name: 'ems_image_info')]
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
            $imageExif = Image::imageExifInfo($tempFile);
            $imageInfo = \array_merge($imageInfo, $imageExif);
            if (isset($imageInfo['widthResolution']) && isset($imageInfo['heightResolution'])) {
                return $imageInfo;
            }
        } catch (\Throwable) {
        }

        try {
            $imageResolution = Image::imageResolution($tempFile);
        } catch (\RuntimeException) {
            return $imageInfo;
        }

        $imageInfo['widthResolution'] = $imageResolution[0];
        $imageInfo['heightResolution'] = $imageResolution[1];

        return $imageInfo;
    }

    /**
     * @return array<mixed>
     */
    #[AsTwigFunction(name: 'ems_json_file')]
    public function jsonFromFile(string $hash): array
    {
        return Json::decode($this->storageManager->getContents($hash));
    }

    #[AsTwigFilter(name: 'ems_temp_file')]
    public function temporaryFile(string $hash): ?string
    {
        if (!$this->storageManager->head($hash)) {
            return null;
        }

        return TempFile::create()
            ->loadFromStream($this->storageManager->getStream($hash))
            ->path;
    }
}
