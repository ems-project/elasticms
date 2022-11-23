<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Factory;

use Doctrine\Bundle\DoctrineBundle\Registry;
use EMS\CommonBundle\Storage\Service\EntityStorage;
use EMS\CommonBundle\Storage\Service\StorageInterface;
use Psr\Log\LoggerInterface;

class EntityFactory extends AbstractFactory implements StorageFactoryInterface
{
    /** @var string */
    public const STORAGE_TYPE = 'db';
    /** @var string */
    public const STORAGE_CONFIG_ACTIVATE = 'activate';
    /** @var LoggerInterface */
    private $logger;
    /** @var bool */
    private $registered = false;
    /** @var Registry */
    private $doctrine;

    public function __construct(LoggerInterface $logger, Registry $doctrine)
    {
        $this->logger = $logger;
        $this->doctrine = $doctrine;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function createService(array $parameters): ?StorageInterface
    {
        $config = $this->resolveParameters($parameters);

        $storageConfigActivate = $config[self::STORAGE_CONFIG_ACTIVATE] ?? true;

        if (false === $storageConfigActivate) {
            return null;
        }

        if ($this->registered) {
            $this->logger->warning('The entity storage service is already registered');

            return null;
        }
        $this->registered = true;

        return new EntityStorage($this->doctrine, $config[self::STORAGE_CONFIG_USAGE], $config[self::STORAGE_CONFIG_HOT_SYNCHRONIZE_LIMIT]);
    }

    public function getStorageType(): string
    {
        return self::STORAGE_TYPE;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array{type: string, activate?: bool, usage: int, hot-synchronize-limit: int}
     */
    private function resolveParameters(array $parameters): array
    {
        $resolver = $this->getDefaultOptionsResolver();
        $resolver
            ->setDefaults([
                self::STORAGE_CONFIG_TYPE => self::STORAGE_TYPE,
                self::STORAGE_CONFIG_ACTIVATE => true,
                self::STORAGE_CONFIG_USAGE => StorageInterface::STORAGE_USAGE_CONFIG_ATTRIBUTE,
            ])
            ->setAllowedTypes(self::STORAGE_CONFIG_ACTIVATE, 'bool')
            ->setAllowedValues(self::STORAGE_CONFIG_TYPE, [self::STORAGE_TYPE])
            ->setAllowedValues(self::STORAGE_CONFIG_ACTIVATE, [true, false])
        ;

        /** @var array{type: string, activate?: bool, usage: int, hot-synchronize-limit: int} $resolvedParameter */
        $resolvedParameter = $resolver->resolve($parameters);

        return $resolvedParameter;
    }
}
