<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle;

use EMS\ClientHelperBundle\DependencyInjection\Compiler\RouterPass;
use Symfony\Cmf\Component\Routing\DependencyInjection\Compiler\RegisterRoutersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EMSClientHelperBundle extends Bundle
{
    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RouterPass());
        $container->addCompilerPass(new RegisterRoutersPass('emsch.routing.chain_router', 'emsch.router'));
    }
}
