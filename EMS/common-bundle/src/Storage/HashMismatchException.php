<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage;

class HashMismatchException extends \RuntimeException
{
    public function __construct(string $hash1, string $hash2)
    {
        parent::__construct(\sprintf('Hash mismatch exception between hashes %s and %s', $hash1, $hash2));
    }
}
