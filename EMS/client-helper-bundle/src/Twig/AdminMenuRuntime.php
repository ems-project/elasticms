<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Twig;

use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use EMS\CommonBundle\Common\EMSLink;
use Twig\Extension\RuntimeExtensionInterface;

final class AdminMenuRuntime implements RuntimeExtensionInterface
{
    private EnvironmentHelper $environmentHelper;

    public function __construct(EnvironmentHelper $environmentHelper)
    {
        $this->environmentHelper = $environmentHelper;
    }

    /**
     * @param EMSLink|string $emsLink
     */
    public function showAdminMenu($emsLink): string
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
