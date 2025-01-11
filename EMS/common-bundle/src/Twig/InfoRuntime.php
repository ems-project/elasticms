<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\Composer\ComposerInfo;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class InfoRuntime implements RuntimeExtensionInterface
{
    public function __construct(private ComposerInfo $composerInfo)
    {
    }

    public function version(string $shortName): string
    {
        return $this->composerInfo->getVersionPackages()[$shortName];
    }
}
