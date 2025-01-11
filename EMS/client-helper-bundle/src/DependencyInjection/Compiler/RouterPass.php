<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RouterPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        // override the default symfony router, with the chain router
        $container->setAlias('router', 'emsch.routing.chain_router');
        $container->getAlias('router')->setPublic(true);
    }
}
