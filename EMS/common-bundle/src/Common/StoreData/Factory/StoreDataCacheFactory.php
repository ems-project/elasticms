<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\StoreData\Factory;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataCacheService;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataServiceInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoreDataCacheFactory implements StoreDataFactoryInterface
{
    final public const string TYPE_CACHE = 'cache';
    public const TTL = 'ttl';

    public function __construct(
        private readonly Cache $cache,
    ) {
    }

    #[\Override]
    public function getType(): string
    {
        return self::TYPE_CACHE;
    }

    #[\Override]
    public function createService(array $parameters): StoreDataServiceInterface
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([self::CONFIG_TYPE])
            ->setDefault(self::TTL, null)
            ->setAllowedTypes(self::CONFIG_TYPE, 'string')
            ->setAllowedTypes(self::TTL, ['null', 'int'])
            ->setAllowedValues(self::CONFIG_TYPE, [self::TYPE_CACHE])
        ;
        /** @var array{ttl: int|null} $options */
        $options = $resolver->resolve($parameters);

        return new StoreDataCacheService($this->cache, $options[self::TTL]);
    }
}
