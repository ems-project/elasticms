<?php

namespace EMS\CommonBundle\Common\StoreData\Factory;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataCacheService;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataServiceInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoreDataCacheFactory implements StoreDataFactoryInterface
{
    public const TYPE_CACHE = 'cache';

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
        ;
        /** @var array{type: string} $config */
        $config = $resolver->resolve($parameters);

        if (self::TYPE_CACHE !== $config[self::CONFIG_TYPE]) {
            throw new \RuntimeException(\sprintf('Type %s not supported by the Cache Factory', $config[self::CONFIG_TYPE]));
        }

        return new StoreDataCacheService($this->cache);
    }
}
