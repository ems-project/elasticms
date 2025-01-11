<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Factory;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Storage\Service\S3Storage;
use EMS\CommonBundle\Storage\Service\StorageInterface;
use Psr\Log\LoggerInterface;

class S3Factory extends AbstractFactory implements StorageFactoryInterface
{
    final public const string STORAGE_TYPE = 's3';
    final public const string STORAGE_CONFIG_CREDENTIALS = 'credentials';
    final public const string STORAGE_CONFIG_BUCKET = 'bucket';
    final public const string STORAGE_CONFIG_UPLOAD_FOLDER = 'upload-folder';
    final public const string STORAGE_CONFIG_MULTIPART_UPLOAD = 'multipart-upload';

    public function __construct(private readonly LoggerInterface $logger, private readonly Cache $cache)
    {
    }

    #[\Override]
    public function createService(array $parameters): ?StorageInterface
    {
        $config = $this->resolveParameters($parameters);

        $credentials = $config[self::STORAGE_CONFIG_CREDENTIALS] ?? null;
        $bucket = $config[self::STORAGE_CONFIG_BUCKET] ?? null;

        if (null === $credentials || 0 === \count($credentials) || null === $bucket || 0 === \strlen($bucket)) {
            @\trigger_error('You should consider to migrate you storage service configuration to the EMS_STORAGES variable', \E_USER_DEPRECATED);

            return null;
        }
        if (null !== $config[self::STORAGE_CONFIG_UPLOAD_FOLDER]) {
            @\trigger_error('The upload-folder S3 config is deprecated', \E_USER_DEPRECATED);
        }

        return new S3Storage($this->logger, $this->cache, $credentials, $bucket, $config[self::STORAGE_CONFIG_USAGE], $config[self::STORAGE_CONFIG_HOT_SYNCHRONIZE_LIMIT], $config[self::STORAGE_CONFIG_MULTIPART_UPLOAD]);
    }

    #[\Override]
    public function getStorageType(): string
    {
        return self::STORAGE_TYPE;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array{type: string, credentials: array<mixed>|null, bucket: string|null, usage: int, hot-synchronize-limit: int, upload-folder: string|null, multipart-upload: bool}
     */
    private function resolveParameters(array $parameters): array
    {
        $resolver = $this->getDefaultOptionsResolver();
        $resolver
            ->setDefaults([
                self::STORAGE_CONFIG_TYPE => self::STORAGE_TYPE,
                self::STORAGE_CONFIG_CREDENTIALS => null,
                self::STORAGE_CONFIG_BUCKET => null,
                self::STORAGE_CONFIG_UPLOAD_FOLDER => null,
                self::STORAGE_CONFIG_MULTIPART_UPLOAD => true,
            ])
            ->setAllowedTypes(self::STORAGE_CONFIG_CREDENTIALS, ['null', 'array'])
            ->setAllowedTypes(self::STORAGE_CONFIG_BUCKET, ['null', 'string'])
            ->setAllowedTypes(self::STORAGE_CONFIG_UPLOAD_FOLDER, ['null', 'string'])
            ->setAllowedTypes(self::STORAGE_CONFIG_MULTIPART_UPLOAD, ['bool'])
            ->setAllowedValues(self::STORAGE_CONFIG_TYPE, [self::STORAGE_TYPE])
        ;
        /** @var array{type: string, credentials: array<mixed>|null, bucket: string|null, usage: int, hot-synchronize-limit: int, upload-folder: string|null, multipart-upload: bool} $resolvedParameter */
        $resolvedParameter = $resolver->resolve($parameters);

        return $resolvedParameter;
    }
}
