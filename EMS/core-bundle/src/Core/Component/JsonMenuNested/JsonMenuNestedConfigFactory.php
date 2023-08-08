<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\JsonMenuNested;

use EMS\CoreBundle\Core\Config\AbstractConfigFactory;
use EMS\CoreBundle\Core\Config\ConfigFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JsonMenuNestedConfigFactory extends AbstractConfigFactory implements ConfigFactoryInterface
{
    /**
     * @param array{ id: string } $options
     */
    protected function create(string $hash, array $options): JsonMenuNestedConfig
    {
        return new JsonMenuNestedConfig($hash, (string) $options['id']);
    }

    /** {@inheritdoc} */
    protected function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['id']);

        return $resolver->resolve($options);
    }
}
