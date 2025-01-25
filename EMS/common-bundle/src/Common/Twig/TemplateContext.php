<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Twig;

use EMS\CommonBundle\Contracts\Twig\TemplateContextInterface;

class TemplateContext implements TemplateContextInterface
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(public array $raw = [])
    {
    }

    #[\Override]
    public function append(array $context): void
    {
        $this->raw = [...$this->raw, ...$context];
    }

    #[\Override]
    public function getRaw(): array
    {
        return $this->raw;
    }
}
