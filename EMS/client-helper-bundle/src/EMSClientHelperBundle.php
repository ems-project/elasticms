<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle;

use EMS\ClientHelperBundle\DependencyInjection\Compiler\RouterPass;
use EMS\ClientHelperBundle\DependencyInjection\EMSClientHelperExtension;
use Symfony\Cmf\Component\Routing\DependencyInjection\Compiler\RegisterRoutersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EMSClientHelperBundle extends AbstractBundle
{
    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RouterPass());
        $container->addCompilerPass(new RegisterRoutersPass('emsch.routing.chain_router', 'emsch.router'));
    }

    #[\Override]
    public function getContainerExtension(): ExtensionInterface
    {
        return new EMSClientHelperExtension();
    }
}
