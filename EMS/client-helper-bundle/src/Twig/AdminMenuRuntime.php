<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Twig;

use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use EMS\CommonBundle\Common\EMSLink;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class AdminMenuRuntime implements RuntimeExtensionInterface
{
    public function __construct(private EnvironmentHelper $environmentHelper)
    {
    }

    public function showAdminMenu(EMSLink|string $emsLink): string
    {
        $backend = $this->environmentHelper->getBackend();

        if (null === $backend || \strlen($backend) <= 0) {
            return '';
        }

        if (!$emsLink instanceof EMSLink) {
            $emsLink = EMSLink::fromText($emsLink);
        }

        return \vsprintf('data-ems-type="%s" data-ems-key="%s" data-ems-url="%s"', [
            $emsLink->getContentType(),
            $emsLink->getOuuid(),
            $backend,
        ]);
    }
}
