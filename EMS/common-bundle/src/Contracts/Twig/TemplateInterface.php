<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Twig;

use Twig\Error\RuntimeError;

interface TemplateInterface
{
    /**
     * @throws RuntimeError
     */
    public function render(): string;

    /**
     * @param array<string, mixed> $context
     */
    public function contextAppend(array $context): self;
}
