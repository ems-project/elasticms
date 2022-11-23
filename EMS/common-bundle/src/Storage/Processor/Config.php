<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Processor;

use EMS\CommonBundle\Common\Standard\Base64;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Storage\FileCollection;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\Standard\Type;
use GuzzleHttp\Psr7\MimeType;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class Config
{
    /** @var string */
    private $assetHash;
    /** @var array<string, mixed> */
    private $options;
    /** @var string */
    private $configHash;
    /** @var string */
    private $cacheKey;
    /** @var ?string */
    private $filename;
    /** @var StorageManager */
    private $storageManager;
    /** @var bool */
    private $cacheableResult;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(StorageManager $storageManager, string $assetHash, string $configHash, array $options = [])
    {
        $this->storageManager = $storageManager;
        $this->assetHash = $assetHash;
        $this->options = $this->resolve($options);
        $this->configHash = $configHash;
        $this->setCacheKeyAndFilename();
        $this->setCacheableResult();

        unset($options[EmsFields::CONTENT_PUBLISHED_DATETIME_FIELD]); // the published date can't invalidate the cache as it'sbased on the config hash now.
    }

    private function makeCacheKey(string $configHash, string $assetHash): string
    {
        return \join(DIRECTORY_SEPARATOR, [
            \substr($configHash, 0, 3),
            \substr($configHash, 3),
            \substr($assetHash, 0, 3),
            \substr($assetHash, 3),
        ]);
    }

    private function setCacheKeyAndFilename(): void
    {
        $this->cacheKey = $this->makeCacheKey($this->configHash, $this->assetHash);
        $this->filename = null;

        if (null === $this->getFileNames()) {
            return;
        }

        foreach ($this->getFileNames() as $filename) {
            if (\is_file($filename)) {
                $this->filename = $filename;
                $this->cacheKey = $this->makeCacheKey($this->configHash, $this->storageManager->computeFileHash($filename));
                break;
            }
        }

        if (null === $this->filename) {
            throw new NotFoundHttpException('File not found');
        }

        if ($this->hasDefaultMimeType()) {
            $this->options[EmsFields::ASSET_CONFIG_MIME_TYPE] = MimeType::fromFilename($this->filename) ?? $this->options[EmsFields::ASSET_CONFIG_MIME_TYPE];
        }
    }

    public function hasDefaultMimeType(): bool
    {
        return \in_array($this->options[EmsFields::ASSET_CONFIG_MIME_TYPE] ?? '', ['application/octet-stream', 'application/bin', '']);
    }

    public function getAssetHash(): string
    {
        return $this->assetHash;
    }

    public function getConfigHash(): string
    {
        return $this->configHash;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Asset_config_type is optional, so _published_datetime can be null.
     */
    public function isValid(\DateTime $lastCacheDate = null): bool
    {
        $publishedDateTime = $this->getLastUpdateDate();

        if ($publishedDateTime && $publishedDateTime < $lastCacheDate) {
            return true;
        }

        return null === $publishedDateTime && null !== $lastCacheDate;
    }

    public function getLastUpdateDate(): ?\DateTime
    {
        $lastUpdateDate = $this->options[EmsFields::CONTENT_PUBLISHED_DATETIME_FIELD] ?? null;

        return $lastUpdateDate instanceof \DateTime ? $lastUpdateDate : null;
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    public function getConfigType(): ?string
    {
        $configType = $this->options[EmsFields::ASSET_CONFIG_TYPE] ?? null;

        return null !== $configType ? (string) $configType : null;
    }

    public function getQuality(): int
    {
        $quality = $this->options[EmsFields::ASSET_CONFIG_QUALITY] ?? null;

        return Type::integer($quality ?? 0);
    }

    /**
     * @return array<string>|null
     */
    public function getFileNames(): ?array
    {
        $fileNames = $this->options[EmsFields::ASSET_CONFIG_FILE_NAMES] ?? null;

        return \is_array($fileNames) ? $fileNames : null;
    }

    public function getBackground(): string
    {
        return (string) $this->options[EmsFields::ASSET_CONFIG_BACKGROUND];
    }

    public function getResize(): ?string
    {
        $resize = $this->options[EmsFields::ASSET_CONFIG_RESIZE] ?? null;

        return null !== $resize ? (string) $resize : null;
    }

    public function getWidth(): int
    {
        return (int) $this->options[EmsFields::ASSET_CONFIG_WIDTH];
    }

    public function getHeight(): int
    {
        return (int) $this->options[EmsFields::ASSET_CONFIG_HEIGHT];
    }

    public function getGravity(): string
    {
        return (string) $this->options[EmsFields::ASSET_CONFIG_GRAVITY];
    }

    public function getRadius(): int
    {
        return (int) $this->options[EmsFields::ASSET_CONFIG_RADIUS];
    }

    public function getRotate(): int
    {
        return (int) $this->options[EmsFields::ASSET_CONFIG_ROTATE];
    }

    public function getAutoRotate(): bool
    {
        return (bool) $this->options[EmsFields::ASSET_CONFIG_AUTO_ROTATE];
    }

    public function getFlipHorizontal(): bool
    {
        return (bool) $this->options[EmsFields::ASSET_CONFIG_FLIP_HORIZONTAL];
    }

    public function getFlipVertical(): bool
    {
        return (bool) $this->options[EmsFields::ASSET_CONFIG_FLIP_VERTICAL];
    }

    /**
     * @return array<string>
     */
    public function getRadiusGeometry(): array
    {
        return \is_array($this->options[EmsFields::ASSET_CONFIG_RADIUS_GEOMETRY]) ? $this->options[EmsFields::ASSET_CONFIG_RADIUS_GEOMETRY] : [];
    }

    public function getBorderColor(): ?string
    {
        $borderColor = $this->options[EmsFields::ASSET_CONFIG_BORDER_COLOR] ?? null;

        return null !== $borderColor ? (string) $borderColor : null;
    }

    public function getDisposition(): string
    {
        return (string) $this->options[EmsFields::ASSET_CONFIG_DISPOSITION];
    }

    public function getWatermark(): ?string
    {
        $watermark = $this->options[EmsFields::ASSET_CONFIG_WATERMARK_HASH] ?? null;

        return null !== $watermark ? (string) $watermark : null;
    }

    public function getMimeType(): string
    {
        return (string) $this->options[EmsFields::ASSET_CONFIG_MIME_TYPE];
    }

    public function getImageFormat(): ?string
    {
        if (isset($this->options[EmsFields::ASSET_CONFIG_IMAGE_FORMAT]) && null !== $this->options[EmsFields::ASSET_CONFIG_IMAGE_FORMAT]) {
            return (string) $this->options[EmsFields::ASSET_CONFIG_IMAGE_FORMAT];
        }

        return null;
    }

    public function isCacheableResult(): bool
    {
        return $this->cacheableResult;
    }

    private function setCacheableResult(): void
    {
        $this->cacheableResult = null !== $this->getCacheContext() && EmsFields::ASSET_CONFIG_TYPE_IMAGE == $this->getConfigType() && \is_string($this->options[EmsFields::ASSET_CONFIG_MIME_TYPE]) && 0 === \strpos($this->options[EmsFields::ASSET_CONFIG_MIME_TYPE], 'image/') && !$this->isSvg();
    }

    public function getCacheContext(): ?string
    {
        if (EmsFields::ASSET_CONFIG_TYPE_IMAGE == $this->getConfigType()) {
            if ($this->isSvg()) {
                return null;
            }

            return $this->getConfigHash();
        }

        return null;
    }

    public function isSvg(): bool
    {
        return \is_string($this->options[EmsFields::ASSET_CONFIG_MIME_TYPE]) ? (bool) \preg_match('/image\/svg.*/', $this->options[EmsFields::ASSET_CONFIG_MIME_TYPE]) : false;
    }

    /**
     * @return FileCollection<array>
     */
    public function getFiles(): FileCollection
    {
        return new FileCollection($this->options[EmsFields::CONTENT_FILES], $this->storageManager);
    }

    public function isAvailabe(): bool
    {
        $before = $this->options[EmsFields::ASSET_CONFIG_BEFORE] ?? 0;
        $after = $this->options[EmsFields::ASSET_CONFIG_AFTER] ?? 0;

        if (\is_string($before)) {
            $beforeTime = \strtotime($before);
            $before = false !== $beforeTime ? $beforeTime : $before;
        }
        $before = \intval($before);

        if (\is_string($after)) {
            $afterTime = \strtotime($after);
            $after = false !== $afterTime ? $afterTime : $after;
        }
        $after = \intval($after);

        $time = \time();
        if (0 !== $before && $time > $before) {
            return false;
        }
        if (0 !== $after && $time < $after) {
            return false;
        }

        return true;
    }

    public function isAuthorized(string $authorization): bool
    {
        $username = $this->options[EmsFields::ASSET_CONFIG_USERNAME] ?? null;
        $password = $this->options[EmsFields::ASSET_CONFIG_PASSWORD] ?? null;

        if (!\is_string($username) || !\is_string($password)) {
            return true;
        }
        if (false === \strpos($authorization, ' ')) {
            return false;
        }
        list($basic, $key) = \explode(' ', $authorization);

        if (0 !== \strcasecmp('Basic', $basic)) {
            throw new \RuntimeException(\sprintf('Unexpected authorization type %s', $basic));
        }
        list($username2, $password2) = \explode(':', Base64::decode($key));

        return $username === $username2 && $password === $password2;
    }

    /**
     * @param array<string, int|string|array<mixed>|bool|\DateTime|null> $options
     *
     * @return array<string, int|string|array<mixed>|bool|\DateTime|null>
     */
    private function resolve(array $options): array
    {
        $defaults = self::getDefaults();

        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults($defaults)
            ->setAllowedTypes(EmsFields::ASSET_CONFIG_URL_TYPE, ['int'])
            ->setAllowedTypes(EmsFields::ASSET_CONFIG_ROTATE, ['float', 'int'])
            ->setAllowedTypes(EmsFields::ASSET_CONFIG_AUTO_ROTATE, ['bool'])
            ->setAllowedTypes(EmsFields::ASSET_CONFIG_FLIP_VERTICAL, ['bool'])
            ->setAllowedTypes(EmsFields::ASSET_CONFIG_FLIP_HORIZONTAL, ['bool'])
            ->setAllowedTypes(EmsFields::ASSET_CONFIG_USERNAME, ['string', 'null'])
            ->setAllowedTypes(EmsFields::ASSET_SEED, ['string', 'null'])
            ->setAllowedTypes(EmsFields::ASSET_CONFIG_PASSWORD, ['string', 'null'])
            ->setAllowedTypes(EmsFields::ASSET_CONFIG_BEFORE, ['string', 'int'])
            ->setAllowedTypes(EmsFields::ASSET_CONFIG_AFTER, ['string', 'int'])
            ->setAllowedTypes(EmsFields::ASSET_CONFIG_IMAGE_FORMAT, ['string', 'null'])
            ->setAllowedValues(EmsFields::ASSET_CONFIG_TYPE, [null, EmsFields::ASSET_CONFIG_TYPE_IMAGE, EmsFields::ASSET_CONFIG_TYPE_ZIP])
            ->setAllowedValues(EmsFields::ASSET_CONFIG_DISPOSITION, [ResponseHeaderBag::DISPOSITION_INLINE, ResponseHeaderBag::DISPOSITION_ATTACHMENT])
            ->setAllowedValues(EmsFields::ASSET_CONFIG_IMAGE_FORMAT, [
                null,
                EmsFields::ASSET_CONFIG_WEBP_IMAGE_FORMAT,
                EmsFields::ASSET_CONFIG_GIF_IMAGE_FORMAT,
                EmsFields::ASSET_CONFIG_BMP_IMAGE_FORMAT,
                EmsFields::ASSET_CONFIG_JPEG_IMAGE_FORMAT,
                EmsFields::ASSET_CONFIG_PNG_IMAGE_FORMAT,
            ])
            ->setAllowedValues(EmsFields::ASSET_CONFIG_RADIUS_GEOMETRY, function ($values) use ($defaults) {
                if (!\is_array($values)) {
                    return false;
                }

                foreach ($values as $value) {
                    if (\is_array($defaults[EmsFields::ASSET_CONFIG_RADIUS_GEOMETRY]) && !\in_array($value, $defaults[EmsFields::ASSET_CONFIG_RADIUS_GEOMETRY])) {
                        throw new UndefinedOptionsException(\sprintf('_radius_geometry %s is invalid (%s)', $value, \implode(',', $defaults[EmsFields::ASSET_CONFIG_RADIUS_GEOMETRY])));
                    }
                }

                return true;
            })
            ->setNormalizer(EmsFields::CONTENT_PUBLISHED_DATETIME_FIELD, function (Options $options, $value) {
                return null !== $value ? new \DateTime($value) : null;
            })
        ;

        return $resolver->resolve($options);
    }

    /**
     * @return array<string, int|string|array<mixed>|bool|\DateTime|null>
     */
    public static function getDefaults(): array
    {
        return [
            EmsFields::ASSET_CONFIG_URL_TYPE => UrlGeneratorInterface::RELATIVE_PATH,
            EmsFields::ASSET_CONFIG_TYPE => null,
            EmsFields::ASSET_CONFIG_FILE_NAMES => null,
            EmsFields::ASSET_CONFIG_QUALITY => 0,
            EmsFields::ASSET_CONFIG_BACKGROUND => '#FFFFFFFF',
            EmsFields::ASSET_CONFIG_RESIZE => 'fill',
            EmsFields::ASSET_CONFIG_WIDTH => 300,
            EmsFields::ASSET_CONFIG_HEIGHT => 200,
            EmsFields::ASSET_CONFIG_GRAVITY => 'center',
            EmsFields::ASSET_CONFIG_RADIUS => null,
            EmsFields::ASSET_CONFIG_RADIUS_GEOMETRY => ['topleft', 'topright', 'bottomright', 'bottomleft'],
            EmsFields::ASSET_CONFIG_BORDER_COLOR => null,
            EmsFields::ASSET_CONFIG_WATERMARK_HASH => null,
            EmsFields::CONTENT_PUBLISHED_DATETIME_FIELD => '2018-02-05T16:08:56+01:00',
            EmsFields::ASSET_CONFIG_MIME_TYPE => 'application/octet-stream',
            EmsFields::ASSET_CONFIG_DISPOSITION => ResponseHeaderBag::DISPOSITION_INLINE,
            EmsFields::ASSET_CONFIG_GET_FILE_PATH => false,
            EmsFields::CONTENT_FILES => [],
            EmsFields::ASSET_CONFIG_ROTATE => 0,
            EmsFields::ASSET_CONFIG_AUTO_ROTATE => true,
            EmsFields::ASSET_CONFIG_FLIP_HORIZONTAL => false,
            EmsFields::ASSET_CONFIG_FLIP_VERTICAL => false,
            EmsFields::ASSET_CONFIG_USERNAME => null,
            EmsFields::ASSET_CONFIG_PASSWORD => null,
            EmsFields::ASSET_CONFIG_BEFORE => 0,
            EmsFields::ASSET_CONFIG_AFTER => 0,
            EmsFields::ASSET_SEED => null,
            EmsFields::ASSET_CONFIG_IMAGE_FORMAT => null,
        ];
    }
}
