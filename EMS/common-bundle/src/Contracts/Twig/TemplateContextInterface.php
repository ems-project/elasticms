<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Twig;

interface TemplateContextInterface
{
    /** @return array<string, mixed> */
    public function getRaw(): array;

    /** @param array<string, mixed> $context */
    public function append(array $context): void;
}
