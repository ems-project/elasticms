<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common;

class KeyStore
{
    /** @param array<string, string> $store */
    public function __construct(
        private readonly array $store = []
    ) {
    }

    public function get(string $key): ?string
    {
        return $this->store[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->store);
    }
}
