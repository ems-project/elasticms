<?php

namespace EMS\CommonBundle\Common\StoreData\Factory;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataCacheService;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataServiceInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoreDataCacheFactory implements StoreDataFactoryInterface
{
    final public const TYPE_CACHE = 'cache';

    public function __construct(
        private readonly Cache $cache
    ) {
    }

    public function getType(): string
    {
        return self::TYPE_CACHE;
    }

    public function createService(array $parameters): StoreDataServiceInterface
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([self::CONFIG_TYPE])
            ->setAllowedTypes(self::CONFIG_TYPE, 'string')
            ->setAllowedValues(self::CONFIG_TYPE, [self::TYPE_CACHE])
        ;
        $resolver->resolve($parameters);

        return new StoreDataCacheService($this->cache);
    }
}
