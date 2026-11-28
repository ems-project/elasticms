<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use Twig\Attribute\AsTwigFunction;
use Twig\Environment as TwigEnvironment;

final readonly class TemplateExtension
{
    public function __construct(private TwigEnvironment $twig)
    {
    }

    #[AsTwigFunction(name: 'ems_template_exists')]
    public function templateExists(string $name): bool
    {
        return $this->twig->getLoader()->exists($name);
    }
}
