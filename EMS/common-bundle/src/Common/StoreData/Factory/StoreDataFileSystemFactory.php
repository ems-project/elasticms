<?php

namespace EMS\CommonBundle\Common\StoreData\Factory;

use EMS\CommonBundle\Common\StoreData\Service\StoreDataFileSystemService;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataServiceInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoreDataFileSystemFactory implements StoreDataFactoryInterface
{
    final public const string TYPE_FS = 'fs';
    final public const string ROOT_PATH = 'path';

    #[\Override]
    public function getType(): string
    {
        return self::TYPE_FS;
    }

    #[\Override]
    public function createService(array $parameters): StoreDataServiceInterface
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([self::CONFIG_TYPE, self::ROOT_PATH])
            ->setAllowedTypes(self::CONFIG_TYPE, 'string')
            ->setAllowedTypes(self::ROOT_PATH, 'string')
            ->setAllowedValues(self::CONFIG_TYPE, [self::TYPE_FS])
        ;
        /** @var array{path: string} $options */
        $options = $resolver->resolve($parameters);

        return new StoreDataFileSystemService($options[self::ROOT_PATH]);
    }
}
