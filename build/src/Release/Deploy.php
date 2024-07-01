<?php

declare(strict_types=1);

namespace Build\Release;

class Deploy
{
    public function __construct(
        public readonly Version $version,
        public readonly Version $previousVersion,
        public readonly string $branch
    ) {
    }
}
