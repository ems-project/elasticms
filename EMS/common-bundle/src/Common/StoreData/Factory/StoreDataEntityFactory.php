<?php

namespace EMS\CommonBundle\Common\StoreData\Factory;

use EMS\CommonBundle\Common\StoreData\Service\StoreDataEntityService;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataServiceInterface;
use EMS\CommonBundle\Repository\StoreDataRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoreDataEntityFactory implements StoreDataFactoryInterface
{
    final public const TYPE_DB = 'db';
    private const TTL = 'ttl';

    public function __construct(
        private readonly StoreDataRepository $repository,
    ) {
    }

    public function getType(): string
    {
        return self::TYPE_DB;
    }

    public function createService(array $parameters): StoreDataServiceInterface
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([self::CONFIG_TYPE])
            ->setDefault(self::TTL, null)
            ->setAllowedTypes(self::CONFIG_TYPE, 'string')
            ->setAllowedTypes(self::TTL, ['null', 'int'])
            ->setAllowedValues(self::CONFIG_TYPE, [self::TYPE_DB])
        ;
        /** @var array{ttl: int|null} $options */
        $options = $resolver->resolve($parameters);

        return new StoreDataEntityService($this->repository, $options[self::TTL]);
    }
}
