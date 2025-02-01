<?php

declare(strict_types=1);

namespace EMS\AdminUIBundle;

use EMS\AdminUIBundle\DependencyInjection\EMSAdminUIExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class EMSAdminUIBundle extends AbstractBundle
{
    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    #[\Override]
    public function getContainerExtension(): ExtensionInterface
    {
        return new EMSAdminUIExtension();
    }
}
