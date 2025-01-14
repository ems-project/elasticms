<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Twig;

use EMS\CommonBundle\Contracts\Twig\TemplateInterface;
use EMS\Helpers\Standard\Json;
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

    public function contextAppend(array $context): self
    {
        $this->context = [...$this->context, ...$context];

        return $this;
    }

    public function json(): array
    {
        return Json::decode($this->render());
    }

    public function render(): string
    {
        try {
            return $this->twig->render($this->template, $this->context);
        } catch (RuntimeError $e) {
            throw $e->getPrevious() instanceof HttpException ? $e->getPrevious() : $e;
        }
    }
}
