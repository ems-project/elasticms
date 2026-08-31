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
    final public const string STORAGE_CONFIG_MULTIPART_UPLOAD = 'multipart-upload';
    final public const string STORAGE_CONFIG_HTTP_OPTIONS = 'http-options';

    public function __construct(private readonly LoggerInterface $logger, private readonly Cache $cache)
    {
    }

    #[\Override]
    public function createService(array $parameters): ?StorageInterface
    {
        $config = $this->resolveParameters($parameters);

        $credentials = $config[self::STORAGE_CONFIG_CREDENTIALS] ?? null;
        $bucket = $config[self::STORAGE_CONFIG_BUCKET] ?? null;

        if (null === $credentials || 0 === \count($credentials)) {
            $this->logger->error('Missing `credentials` config for S3 storage');

            return null;
        }
        if (null === $bucket || '' === (string) $bucket) {
            $this->logger->error('Missing `bucket` config for S3 storage');

            return null;
        }

        return new S3Storage($this->logger, $this->cache, $credentials, $bucket, $config[self::STORAGE_CONFIG_USAGE], $config[self::STORAGE_CONFIG_HOT_SYNCHRONIZE_LIMIT], $config[self::STORAGE_CONFIG_MULTIPART_UPLOAD], $config[self::STORAGE_CONFIG_HTTP_OPTIONS]);
    }

    #[\Override]
    public function getStorageType(): string
    {
        return self::STORAGE_TYPE;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array{type: string, credentials: array<mixed>|null, bucket: string|null, usage: int, hot-synchronize-limit: int, upload-folder: string|null, multipart-upload: bool, http-options: array<mixed>}
     */
    private function resolveParameters(array $parameters): array
    {
        $resolver = $this->getDefaultOptionsResolver();
        $resolver
            ->setDefaults([
                self::STORAGE_CONFIG_TYPE => self::STORAGE_TYPE,
                self::STORAGE_CONFIG_CREDENTIALS => null,
                self::STORAGE_CONFIG_BUCKET => null,
                self::STORAGE_CONFIG_MULTIPART_UPLOAD => true,
                self::STORAGE_CONFIG_HTTP_OPTIONS => [
                    'connect_timeout' => 1.0,
                    'timeout' => 5.0,
                    'retries' => 0,
                ],
            ])
            ->setAllowedTypes(self::STORAGE_CONFIG_CREDENTIALS, ['null', 'array'])
            ->setAllowedTypes(self::STORAGE_CONFIG_BUCKET, ['null', 'string'])
            ->setAllowedTypes(self::STORAGE_CONFIG_MULTIPART_UPLOAD, ['bool'])
            ->setAllowedValues(self::STORAGE_CONFIG_TYPE, [self::STORAGE_TYPE])
            ->setAllowedTypes(self::STORAGE_CONFIG_HTTP_OPTIONS, ['array'])
        ;
        /** @var array{type: string, credentials: array<mixed>|null, bucket: string|null, usage: int, hot-synchronize-limit: int, upload-folder: string|null, multipart-upload: bool, http-options: array<mixed>} $resolvedParameter */
        $resolvedParameter = $resolver->resolve($parameters);

        return $resolvedParameter;
    }
}
