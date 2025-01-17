<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Twig;

use EMS\CommonBundle\Contracts\Twig\TemplateInterface;
use EMS\Helpers\Standard\Json;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Twig\TemplateWrapper;

class Template implements TemplateInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private readonly TemplateWrapper $template,
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

    public function jsonBlock(string $name): array
    {
        return $this->template->hasBlock($name) ? Json::decode($this->renderBlock($name) ?? '{}') : [];
    }

    public function render(): string
    {
        try {
            return $this->template->render($this->context);
        } catch (\Throwable $e) {
            throw $e->getPrevious() instanceof HttpException ? $e->getPrevious() : $e;
        }
    }

    public function renderBlock(string $name): ?string
    {
        try {
            if (!$this->template->hasBlock($name)) {
                return null;
            }

            return $this->template->renderBlock($name, $this->context);
        } catch (\Throwable $e) {
            throw $e->getPrevious() instanceof HttpException ? $e->getPrevious() : $e;
        }
    }
}
