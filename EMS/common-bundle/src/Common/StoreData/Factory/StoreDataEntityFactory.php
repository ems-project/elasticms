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
        ;
        /** @var array{type: string} $config */
        $config = $resolver->resolve($parameters);

        if (self::TYPE_DB !== $config[self::CONFIG_TYPE]) {
            throw new \RuntimeException(\sprintf('Type %s not supported by the Entity Factory', $config[self::CONFIG_TYPE]));
        }

        return new StoreDataEntityService($this->repository);
    }
}
