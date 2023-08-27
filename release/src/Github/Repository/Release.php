<?php

declare(strict_types=1);

namespace EMS\Release\Github\Repository;

class Release
{
    public function __construct(
        public readonly Repository $repository,
        public readonly string $tag,
        public readonly string $createdAt,
        public readonly string $draft,
        public readonly ?string $sha,
    ) {
    }

    public function url(): string
    {
        $repositoryUrl = $this->repository->url();

        return "$repositoryUrl/releases/tag/$this->tag";
    }
}
