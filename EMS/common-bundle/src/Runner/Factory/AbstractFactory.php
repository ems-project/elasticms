<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner\Factory;

use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFactory implements RunnerFactoryInterface
{
    public function __construct(readonly protected LoggerInterface $logger)
    {
    }

    protected function getDefaultOptionsResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired(self::RUNNER_CONFIG_TYPE)
            ->setRequired(self::RUNNER_CONFIG_TAG)
            ->setAllowedTypes(self::RUNNER_CONFIG_TYPE, ['string'])
            ->setAllowedTypes(self::RUNNER_CONFIG_TAG, ['string'])
        ;

        return $resolver;
    }
}
