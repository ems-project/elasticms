<?php

declare(strict_types=1);

namespace Build\Release\File;

class ComposerPackage
{
    public function __construct(
        public readonly string $name,
        public readonly string $version,
        public readonly string $sha,
        public readonly \DateTimeImmutable $date,
    ) {
    }
}
