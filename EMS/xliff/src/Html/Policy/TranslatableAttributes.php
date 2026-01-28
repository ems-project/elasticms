<?php

declare(strict_types=1);

namespace EMS\Xliff\Html\Policy;

final class TranslatableAttributes
{
    /** @var array<string, true> */
    private array $whitelist;

    /**
     * @param string[]|null $whitelist
     */
    public function __construct(?array $whitelist = null)
    {
        $whitelist ??= [
            'title', 'alt', 'placeholder',
            'aria-label', 'aria-labelledby', 'aria-describedby',
            'value',
        ];

        $this->whitelist = \array_fill_keys($whitelist, true);
    }

    public function isTranslatable(string $tagName, string $attrName): bool
    {
        if ('value' === $attrName && !\in_array($tagName, ['submit', 'button', 'reset'], true)) {
            return false;
        }

        return isset($this->whitelist[\strtolower($attrName)]);
    }
}
