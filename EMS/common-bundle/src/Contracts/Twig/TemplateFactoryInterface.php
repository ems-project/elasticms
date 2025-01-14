<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Twig;

interface TemplateFactoryInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function create(string $templateName, array $context = []): TemplateInterface;
}
