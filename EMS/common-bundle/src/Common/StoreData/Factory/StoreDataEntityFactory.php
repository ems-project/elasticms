<?php

namespace EMS\CommonBundle\Common\StoreData\Factory;

use EMS\CommonBundle\Common\StoreData\Service\StoreDataEntityService;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataServiceInterface;
use EMS\CommonBundle\Repository\StoreDataRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoreDataEntityFactory implements StoreDataFactoryInterface
{
    public const TYPE_DB = 'db';

    public function __construct(
        private readonly StoreDataRepository $repository
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
            ->setAllowedTypes(self::CONFIG_TYPE, 'string')
            ->setAllowedValues(self::CONFIG_TYPE, [self::TYPE_DB])
        ;
        $resolver->resolve($parameters);

        return new StoreDataEntityService($this->repository);
    }
}
