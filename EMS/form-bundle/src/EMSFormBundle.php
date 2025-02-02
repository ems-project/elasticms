<?php

declare(strict_types=1);

namespace EMS\FormBundle;

use EMS\FormBundle\DependencyInjection\EMSFormExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class EMSFormBundle extends AbstractBundle
{
    #[\Override]
    public function getContainerExtension(): ExtensionInterface
    {
        return new EMSFormExtension();
    }
}
