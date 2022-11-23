<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\Composer\ComposerInfo;
use Twig\Extension\RuntimeExtensionInterface;

final class InfoRuntime implements RuntimeExtensionInterface
{
    private ComposerInfo $composerInfo;

    public function __construct(ComposerInfo $composerInfo)
    {
        $this->composerInfo = $composerInfo;
    }

    public function version(string $shortName): string
    {
        return $this->composerInfo->getVersionPackages()[$shortName];
    }
}
