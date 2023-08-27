<?php

declare(strict_types=1);

namespace EMS\Release\Github;

use EMS\Release\Github\Repository\Repository;

class RepositoryCollection
{
    /** @var Repository[] */
    private array $repositories;
    /** @var array<string, Repository[]> */
    private array $grouped;

    /**
     * @param array<array{ name: string, group: string, package?: string,}> $githubRepos
     */
    public function __construct(GithubApi $githubApi, array $githubRepos)
    {
        foreach ($githubRepos as $githubRepo) {
            $repository = new Repository($githubApi, $githubRepo['name'], $githubRepo['group']);
            $repository->package = $githubRepo['package'] ?? null;

            $this->repositories[$repository->name] = $repository;
            $this->grouped[$repository->group][$repository->name] = $repository;
        }
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return \array_keys($this->repositories);
    }

    /**
     * @return array<string, Repository[]>
     */
    public function grouped(): array
    {
        return $this->grouped;
    }

    public function get(string $name): Repository
    {
        return $this->repositories[$name];
    }
}
