<?php

declare(strict_types=1);

namespace EMS\Release\Github\Repository;

use EMS\Release\Github\GithubApi;

class Repository
{
    private string $organization;
    public string $name;

    public ?string $package;
    public ?string $group;

    public function __construct(
        private readonly GithubApi $githubApi,
        string $name
    ) {
        [$organization, $repository] = \explode('/', $name);
        $this->organization = $organization;
        $this->name = $repository;
    }

    public function url(): string
    {
        return "https://github.com/$this->organization/$this->name";
    }

    /**
     * @return Release[]
     */
    public function getReleases(): array
    {
        $tags = $this->getTags();

        $releases = [];
        /** @var array<array{ tag_name: string, created_at: string, draft: bool }> $results */
        $results = $this->githubApi->api->repo()->releases()->all($this->organization, $this->name);

        foreach ($results as $result) {
            $createdAt = \DateTimeImmutable::createFromFormat(DATE_ATOM, $result['created_at']);

            $release = new Release(
                $this,
                $result['tag_name'],
                $createdAt ? $createdAt->format('d/m/Y H:i:s') : '???',
                $result['draft'],
                $tags[$result['tag_name']] ?? null
            );

            $releases[$release->tag] = $release;
        }

        $this->sortByVersion($releases);

        return \array_reverse($releases);
    }

    /**
     * @return array<string, string>
     */
    private function getTags(): array
    {
        $tags = [];
        $results = $this->githubApi->api->repo()->tags($this->organization, $this->name);

        foreach ($results as $result) {
            $tags[(string) $result['name']] = $result['commit']['sha'];
        }

        $this->sortByVersion($tags);

        return $tags;
    }

    /**
     * @param array<string, string> $results
     */
    private function sortByVersion(array &$results): void
    {
        /** @var callable(string $a, string $b): int $comparer */
        $comparer = 'version_compare';

        \uksort($results, $comparer);
    }
}
