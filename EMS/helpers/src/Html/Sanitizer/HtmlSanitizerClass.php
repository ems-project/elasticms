<?php

declare(strict_types=1);

namespace EMS\Helpers\Html\Sanitizer;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

class HtmlSanitizerClass implements AttributeSanitizerInterface
{
    /**
     * @param array<mixed>|array{ allow: string[], drop: string[], replace: string[]} $settings
     */
    public function __construct(private readonly array $settings = [])
    {
    }

    #[\Override]
    public function getSupportedElements(): ?array
    {
        return null;
    }

    #[\Override]
    public function getSupportedAttributes(): ?array
    {
        return ['class'];
    }

    #[\Override]
    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        $classes = \explode(' ', $value);
        $classNames = \array_filter(\array_map('trim', $classes));

        if (\count($this->settings['replace']) > 0) {
            $classNames = \array_map(fn (string $className) => $this->settings['replace'][$className] ?? $className, $classNames);
        }

        if (\count($this->settings['allow']) > 0) {
            $classNames = \array_filter($classNames, fn (string $className) => \in_array($className, $this->settings['allow']));
        }

        if (\count($this->settings['drop']) > 0) {
            $classNames = \array_filter($classNames, fn (string $className) => !\in_array($className, $this->settings['drop']));
        }

        return \count($classNames) > 0 ? \implode(' ', $classNames) : null;
    }
}
