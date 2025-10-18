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

    public function get(string $key): string
    {
        if (!\array_key_exists($key, $this->store)) {
            throw new \RuntimeException(\sprintf('Key "%s" does not exist in EMS_KEY_STORE', $key));
        }

        return $this->store[$key];
    }
}
