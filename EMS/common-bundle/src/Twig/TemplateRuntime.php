<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use Twig\Environment as TwigEnvironment;
use Twig\Extension\RuntimeExtensionInterface;

final class TemplateRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly TwigEnvironment $twig)
    {
    }

    public function templateExists(string $name): bool
    {
        return $this->twig->getLoader()->exists($name);
    }
}
