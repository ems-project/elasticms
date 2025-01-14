<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Twig;

use EMS\CommonBundle\Contracts\Twig\TemplateInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Twig\Environment;
use Twig\Error\RuntimeError;

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

    public function response(): Response
    {
        try {
            return new Response($this->render());
        } catch (RuntimeError $e) {
            throw $e->getPrevious() instanceof HttpException ? $e->getPrevious() : $e;
        }
    }

    public function contextAppend(array $context): self
    {
        $this->context = [...$this->context, ...$context];

        return $this;
    }
}
