<?php

declare(strict_types=1);

namespace EMS\AdminUIBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EMSAdminUIBundle extends Bundle
{
    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }
}
