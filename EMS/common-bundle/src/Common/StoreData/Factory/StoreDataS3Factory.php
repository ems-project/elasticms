<?php

namespace EMS\CommonBundle\Common\StoreData\Factory;

use EMS\CommonBundle\Common\StoreData\Service\StoreDataS3Service;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataServiceInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoreDataS3Factory implements StoreDataFactoryInterface
{
    public const TYPE_S3 = 's3';
    public const CREDENTIALS = 'credentials';
    public const BUCKET = 'bucket';
    public const TTL = 'ttl';

    public function getType(): string
    {
        return self::TYPE_S3;
    }

    public function createService(array $parameters): StoreDataServiceInterface
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([self::CREDENTIALS, self::BUCKET])
            ->setAllowedTypes(self::CONFIG_TYPE, 'string')
            ->setAllowedTypes(self::CREDENTIALS, 'array')
            ->setAllowedTypes(self::BUCKET, 'string')
            ->setAllowedTypes(self::TTL, 'null|int')
            ->setAllowedValues(self::CONFIG_TYPE, [self::TYPE_S3])
            ->setDefault(self::TTL, null)
        ;
        /** @var array{credentials: array<string, mixed>, bucket: string, ttl: int|null} $options */
        $options = $resolver->resolve($parameters);

        return new StoreDataS3Service($options[self::CREDENTIALS], $options[self::BUCKET], $options[self::TTL]);
    }
}
