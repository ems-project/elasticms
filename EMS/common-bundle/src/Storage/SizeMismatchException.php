<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage;

class SizeMismatchException extends \RuntimeException
{
    public function __construct(string $hash, int $size1, int $size2)
    {
        parent::__construct(\sprintf('Size mismatch exception for the hash %s : %d vs %d', $hash, $size1, $size2));
    }
}
