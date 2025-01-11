<?php

namespace EMS\CommonBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EMSCommonBundle extends Bundle
{
    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }
}
