<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\Composer\ComposerInfo;
use Twig\Attribute\AsTwigFunction;

readonly class InfoExtension
{
    public function __construct(private ComposerInfo $composerInfo)
    {
    }

    #[AsTwigFunction(name: 'ems_version')]
    public function version(string $shortName): string
    {
        return $this->composerInfo->getVersionPackages()[$shortName];
    }
}
