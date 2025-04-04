<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner\Factory;

use EMS\CommonBundle\Common\Composer\ComposerInfo;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFactory implements RunnerFactoryInterface
{
    public function __construct(readonly protected LoggerInterface $logger, readonly private ComposerInfo $composerInfo)
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

    public function getCommonVersionTag(?string $imageTag): ?string
    {
        if (self::RUNNER_OPENSHIFT_EMS_VERSION_REPLACER === $imageTag) {
            $imageTag = $this->composerInfo->getVersionPackages()['common'] ?? null;
            if (null === $imageTag) {
                $this->logger->warning('ElasticMS\'s version package is not configured.');
            }
        }

        return $imageTag;
    }
}
