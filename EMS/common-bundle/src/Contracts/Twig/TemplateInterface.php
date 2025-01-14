<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Twig;

use Symfony\Component\HttpFoundation\Response;
use Twig\Error\RuntimeError;

interface TemplateInterface
{
    /**
     * @throws RuntimeError
     */
    public function render(): string;

    public function response(): Response;

    /**
     * @param array<string, mixed> $context
     */
    public function contextAppend(array $context): self;
}
