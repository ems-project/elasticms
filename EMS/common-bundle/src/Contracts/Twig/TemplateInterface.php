<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Twig;

interface TemplateInterface
{
    public function context(): TemplateContextInterface;

    public function render(): string;

    public function renderBlock(string $name): ?string;

    /**
     * @return array<string, mixed>
     */
    public function json(): array;

    /**
     * @return array<string, mixed>
     */
    public function jsonBlock(string $name): array;
}
