<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Twig;

use EMS\CommonBundle\Contracts\Twig\TemplateFactoryInterface;
use EMS\CommonBundle\Contracts\Twig\TemplateInterface;
use Twig\Environment;

readonly class TemplateFactory implements TemplateFactoryInterface
{
    public function __construct(private Environment $twig)
    {
    }

    public function create(string $templateName, array $context = []): TemplateInterface
    {
        $template = $this->twig->load($templateName);

        return new Template($template, $context);
    }
}
