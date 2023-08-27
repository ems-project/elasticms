<?php

declare(strict_types=1);

namespace EMS\Release\Github\Repository;

use EMS\Release\Github\GithubApi;

class Repository
{
    private string $organization;
    public readonly string $name;
    public ?string $package;

    public ?Release $latestRelease = null;

    public function __construct(
        private readonly GithubApi $githubApi,
        string $name,
        public readonly string $group
    ) {
        [$organization, $repository] = \explode('/', $name);
        $this->organization = $organization;
        $this->name = $repository;
    }

    public function url(): string
    {
        return "https://github.com/$this->organization/$this->name";
    }

    public function getLastRelease(): Release
    {
        $query = <<<QUERY
query {
  viewer { login }
  rateLimit { limit cost remaining resetAt }
}
QUERY;



        $test = $this->githubApi->graphql()->execute($query);



//        if ($this->latestRelease) {
//            return $this->latestRelease;
//        }
//
//    //    $tags = $this->getTags();
//        /** @var array<array{ tag_name: string, created_at: string, draft: bool }> $results */
//        $response = $this->githubApi->api->repo()->releases()->latest($this->organization, $this->name);
//
//        $tags = $this->githubApi->api->repos()->tags($this->organization, $this->name, [
//            'per_page' => 30,
//            'sort' => 'name',
//            'direction' => 'DESC'
//        ]);
//
//
//   //     $this->latestRelease = $this->responseToRelease($response, $tags);
//
//        return $this->latestRelease;
    }

    /**
     * @return Release[]
     */
    public function getReleases(): array
    {
    //    $tags = $this->getTags();

        $releases = [];
        /** @var array<array{ tag_name: string, created_at: string, draft: bool }> $responses */
        $responses = $this->githubApi->api->repo()->releases()->all($this->organization, $this->name);

        foreach ($responses as $response) {
            $release = $this->responseToRelease($response);
            $releases[$release->tag] = $release;
        }

        $this->sortByVersion($releases);

        return \array_reverse($releases);
    }

    /**
     * @param array{ tag_name: string, created_at: string, draft: bool } $response
     */
    private function responseToRelease(array $response): Release
    {
        $createdAt = \DateTimeImmutable::createFromFormat(DATE_ATOM, $response['created_at']);

        if (!isset($tags[$response['tag_name']])) {
            $test = 1;
        }

        return new Release(
            $this,
            $response['tag_name'],
            $createdAt ? $createdAt->format('d/m/Y H:i:s') : '???',
            $response['draft'] ? 'yes' : 'no',
            $tags[$response['tag_name']] ?? null
        );
    }

    /**
     * @return array<string, string>
     */
    private function getTags(): array
    {
        $tags = [];

        $results = $this->githubApi->api->repos()->tags($this->organization, $this->name, [
            'per_page' => 30,
            'sort' => 'name',
            'direction' => 'DESC'
        ]);


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
