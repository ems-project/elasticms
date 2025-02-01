<?php

declare(strict_types=1);

namespace EMS\CommonBundle;

use EMS\CommonBundle\DependencyInjection\EMSCommonExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class EMSCommonBundle extends AbstractBundle
{
    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    #[\Override]
    public function getContainerExtension(): ExtensionInterface
    {
        return new EMSCommonExtension();
    }
}
