<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Cache;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class Config
{
    public string $type;
    public string $prefix;
    /** @var array{host: string, port: int} */
    public array $redis;

    /**
     * @param array<mixed> $config
     */
    public function __construct(array $config)
    {
        $resolver = $this->configResolver();

        $resolvedConfig = $resolver->resolve($config);

        $this->type = $resolvedConfig['type'];
        $this->prefix = $resolvedConfig['prefix'];
        $this->redis = $resolvedConfig['redis'];
    }

    private function configResolver(): OptionsResolver
    {
        $configResolver = new OptionsResolver();
        $configResolver
            ->setRequired(['type', 'prefix'])
            ->setAllowedValues('type', Cache::TYPES)
            ->setDefault('redis', function (OptionsResolver $redisResolver, Options $config) {
                $redisResolver
                    ->setDefault('host', 'localhost')
                    ->setDefault('port', 6379)
                    ->setAllowedTypes('host', ['string'])
                    ->setAllowedTypes('port', ['integer'])
                ;

                if (Cache::TYPE_REDIS === $config['type']) {
                    $redisResolver->setRequired(['host', 'port']);
                }
            })
        ;

        return $configResolver;
    }
}
