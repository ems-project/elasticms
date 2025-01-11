<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Factory;

use EMS\CommonBundle\Common\CoreApi\TokenStore;
use EMS\CommonBundle\Storage\Service\ApiStorage;
use EMS\CommonBundle\Storage\Service\StorageInterface;
use Psr\Log\LoggerInterface;

class ApiFactory extends AbstractFactory implements StorageFactoryInterface
{
    final public const string STORAGE_TYPE = 'api';

    public function __construct(private readonly LoggerInterface $logger, private readonly TokenStore $tokenStore)
    {
    }

    #[\Override]
    public function createService(array $parameters): ?StorageInterface
    {
        $config = $this->resolveParameters($parameters);

        return new ApiStorage($this->logger, $this->tokenStore, $config[self::STORAGE_CONFIG_USAGE], $config[self::STORAGE_CONFIG_HOT_SYNCHRONIZE_LIMIT]);
    }

    #[\Override]
    public function getStorageType(): string
    {
        return self::STORAGE_TYPE;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array{type: string, usage: int, hot-synchronize-limit: int}
     */
    private function resolveParameters(array $parameters): array
    {
        $resolver = $this->getDefaultOptionsResolver();
        $resolver
            ->setDefaults([
                self::STORAGE_CONFIG_TYPE => self::STORAGE_TYPE,
            ])
            ->setAllowedValues(self::STORAGE_CONFIG_TYPE, [self::STORAGE_TYPE])
        ;
        /** @var array{type: string, usage: int, hot-synchronize-limit: int} $resolvedParameter */
        $resolvedParameter = $resolver->resolve($parameters);

        return $resolvedParameter;
    }
}
