<?php

declare(strict_types=1);

namespace EMS\Helpers\Html\Sanitizer;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\UrlAttributeSanitizer;

use function Symfony\Component\String\u;

class HtmlSanitizerLink implements AttributeSanitizerInterface
{
    #[\Override]
    public function getSupportedElements(): ?array
    {
        return null;
    }

    #[\Override]
    public function getSupportedAttributes(): ?array
    {
        return ['src', 'href', 'lowsrc', 'background', 'ping'];
    }

    #[\Override]
    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        if (u($value)->startsWith('ems://')) {
            return $value;
        }

        $urlSanitizer = new UrlAttributeSanitizer();

        return $urlSanitizer->sanitizeAttribute($element, $attribute, $value, $config);
    }
}
