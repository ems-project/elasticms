<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Twig;

use Twig\Error\RuntimeError;

interface TemplateInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function contextAppend(array $context): self;

    /**
     * @throws RuntimeError
     */
    public function render(): string;

    /**
     * @return array<string, mixed>
     */
    public function json(): array;
}
