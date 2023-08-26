<?php

declare(strict_types=1);

namespace EMS\Release\Github;

use EMS\Release\Github\Repository\Repository;

class RepositoryCollection
{
    /** @var Repository[] */
    private array $repositories;

    /**
     * @param array<array{ name: string, package?: string, group?: string}> $githubRepos
     */
    public function __construct(GithubApi $githubApi, array $githubRepos)
    {
        foreach ($githubRepos as $githubRepo) {
            $repository = new Repository($githubApi, $githubRepo['name']);
            $repository->package = $githubRepo['package'] ?? null;
            $repository->group = $githubRepo['group'] ?? null;

            $this->repositories[$repository->name] = $repository;
        }
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return \array_keys($this->repositories);
    }

    public function get(string $name): Repository
    {
        return $this->repositories[$name];
    }
}
