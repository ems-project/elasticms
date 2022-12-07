<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

use EMS\CoreBundle\Core\Config\ConfigInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaLibraryConfig implements ConfigInterface
{
    /**
     * @param array<mixed> $options
     */
    public function __construct(public readonly array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'contentTypeName',
        ]);
        $resolver->resolve($options);
    }
}
