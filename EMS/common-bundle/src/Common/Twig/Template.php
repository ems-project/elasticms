<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Twig;

use EMS\CommonBundle\Contracts\Twig\TemplateInterface;
use Twig\Environment;

class Template implements TemplateInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private readonly Environment $twig,
        private readonly string $template,
        private array $context = [],
    ) {
    }

    public function render(): string
    {
        return $this->twig->render($this->template, $this->context);
    }

    public function contextAppend(array $context): self
    {
        $this->context = [...$this->context, ...$context];

        return $this;
    }
}
