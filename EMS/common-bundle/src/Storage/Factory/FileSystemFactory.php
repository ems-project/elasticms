<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Factory;

use EMS\CommonBundle\Storage\Service\FileSystemStorage;
use EMS\CommonBundle\Storage\Service\StorageInterface;
use EMS\Helpers\File\Folder;
use Psr\Log\LoggerInterface;

class FileSystemFactory extends AbstractFactory implements StorageFactoryInterface
{
    final public const string STORAGE_TYPE = 'fs';
    final public const string STORAGE_CONFIG_PATH = 'path';
    /** @var string[] */
    private array $usedFolder = [];

    public function __construct(private readonly LoggerInterface $logger, private readonly string $projectDir)
    {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[\Override]
    public function createService(array $parameters): ?StorageInterface
    {
        $config = $this->resolveParameters($parameters);

        $path = $config[self::STORAGE_CONFIG_PATH] ?? null;

        if ('' === $path || null === $path) {
            @\trigger_error('You should consider to migrate you storage service configuration to the EMS_STORAGES variable', \E_USER_DEPRECATED);

            return null;
        }

        if (\str_starts_with($path, '.')) {
            $path = $this->projectDir.DIRECTORY_SEPARATOR.$path;
        }

        $realPath = Folder::getRealPath($path);

        if (\in_array($realPath, $this->usedFolder)) {
            $this->logger->warning('The folder {realPath} is already used by another storage service', [$realPath]);

            return null;
        }

        $this->usedFolder[] = $realPath;

        return new FileSystemStorage($this->logger, $realPath, $config[self::STORAGE_CONFIG_USAGE], $config[self::STORAGE_CONFIG_HOT_SYNCHRONIZE_LIMIT]);
    }

    #[\Override]
    public function getStorageType(): string
    {
        return self::STORAGE_TYPE;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array{type: string, path?: string, usage: int, hot-synchronize-limit: int}
     */
    private function resolveParameters(array $parameters): array
    {
        $resolver = $this->getDefaultOptionsResolver();
        $resolver
            ->setDefaults([
                self::STORAGE_CONFIG_TYPE => self::STORAGE_TYPE,
                self::STORAGE_CONFIG_PATH => null,
            ])
            ->setAllowedValues(self::STORAGE_CONFIG_TYPE, [self::STORAGE_TYPE])
            ->setRequired(self::STORAGE_CONFIG_PATH)
            ->setAllowedTypes(self::STORAGE_CONFIG_PATH, 'string')
        ;

        /** @var array{type: string, path?: string, usage: int, hot-synchronize-limit: int} $resolvedParameter */
        $resolvedParameter = $resolver->resolve($parameters);

        return $resolvedParameter;
    }
}
