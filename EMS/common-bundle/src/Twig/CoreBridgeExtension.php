<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;
use Twig\Attribute\AsTwigFunction;

readonly class CoreBridgeExtension
{
    public function __construct(private CoreBridgeInterface $coreBridge)
    {
    }

    #[AsTwigFunction(name: 'ems_core')]
    public function build(): CoreBridgeInterface
    {
        return $this->coreBridge;
    }
}
