<?php

declare(strict_types=1);

namespace Build\Release\Service;

use Build\Release\Version;

class GithubRelease
{
    public ?string $status = null;
    public ?string $packagistSha = null;

    public function __construct(
        public readonly int $id,
        public readonly string $repository,
        public readonly string $url,
        public readonly Version $version,
        public readonly string $sha,
        public readonly \DateTimeImmutable $publishedAt,
        public readonly bool $isDraft,
        public readonly bool $isPrerelease,
    ) {
    }

    public function isPublished(): bool
    {
        if (null === $this->packagistSha) {
            return false;
        }

        return $this->packagistSha === $this->sha;
    }
}
