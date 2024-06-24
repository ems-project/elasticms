<?php

declare(strict_types=1);

namespace Build\Release\Service;

use Build\Release\Deploy;
use Build\Release\Version;
use EMS\Helpers\Standard\DateTime;
use Github\AuthMethod;
use Github\Client as ClientGithub;

class GithubApiService
{
    private ClientGithub $api;
    /** @var ?string[] */
    private ?array $tags = null;

    protected const ORG = 'ems-project';
    protected const REPO = 'elasticms';

    public function __construct()
    {
        if (null === $token = $_SERVER['GITHUB_API_TOKEN'] ?? null) {
            throw new \RuntimeException('GITHUB_API_TOKEN not defined!');
        }

        $this->api = new ClientGithub();
        $this->api->authenticate($token, AuthMethod::JWT);
    }

    public function createRelease(Deploy $deploy, string $repository = self::REPO): GithubRelease
    {
        $releaseNotes = $this->getReleaseNotes($deploy, $repository);
        $this->api->repo()->releases()->create(self::ORG, $repository, [
            'tag_name' => $deploy->version->getTag(),
            'target_commitish' => $deploy->branch,
            'name' => $releaseNotes['name'],
            'body' => $releaseNotes['body'],
            'make_latest' => 'true',
        ]);

        if (null === $release = $this->getRelease($deploy->version, $repository)) {
            throw new \RuntimeException(\sprintf('Could not retrieve release (%s %s)', $repository, $deploy->version->getTag()));
        }

        return $release;
    }

    public function getBranch(string $branchName): string
    {
        try {
            $branch = $this->api->repo()->branches(self::ORG, self::REPO, $branchName);

            return $branch['name'];
        } catch (\Throwable $e) {
            throw 404 === $e->getCode() ? new \RuntimeException('Branch not found!') : $e;
        }
    }

    public function getRelease(Version $version, string $repository = self::REPO): ?GithubRelease
    {
        try {
            $release = $this->api->repo()->releases()->tag(self::ORG, $repository, $version->getTag());
            $ref = $this->api->git()->references()->show(self::ORG, $repository, 'tags/'.$version->getTag());

            return new GithubRelease(
                id: $release['id'],
                repository: $repository,
                url: $release['html_url'],
                version: Version::fromTag($release['tag_name']),
                sha: $ref['object']['sha'],
                publishedAt: (DateTime::createFromFormat($release['published_at'])),
                isDraft: true === $release['draft'],
                isPrerelease: true === $release['prerelease']
            );
        } catch (\Throwable $e) {
            if (404 === $e->getCode()) {
                return null;
            }
            throw $e;
        }
    }

    public function deleteRelease(GithubRelease $release): void
    {
        $this->api->repo()->releases()->remove(self::ORG, $release->repository, $release->id);
        $this->api->git()->references()->remove(self::ORG, $release->repository, 'tags/'.$release->version->getTag());
    }

    public function getPreviousVersion(Version $version): Version
    {
        $tags = $this->getTags();

        if ('patch' === $version->getType()) {
            $previousVersion = new Version($version->major, $version->minor, $version->patch - 1);

            if (!\in_array($previousVersion->getTag(), $tags, true)) {
                throw new \RuntimeException('Previous version not found!');
            }

            return $previousVersion;
        }

        $versionTag = $version->getTag();

        if (!\in_array($versionTag, $tags, true)) {
            $tags[] = $versionTag;
        }

        \usort($tags, static fn (string $a, string $b): int => \version_compare($b, $a));
        $versionIndex = \array_search($versionTag, $tags, true);

        $previousVersion = $tags[++$versionIndex] ?? null;
        if (null === $previousVersion) {
            throw new \RuntimeException('Could not determine previous version!');
        }

        return Version::fromTag($previousVersion);
    }

    /**
     * @return array{'name': string, 'body': string}
     */
    public function getReleaseNotes(Deploy $deploy, string $repository = self::REPO): array
    {
        $response = $this->api->repo()->releases()->generateNotes(self::ORG, $repository, \array_filter([
            'tag_name' => $deploy->version->getTag(),
            'target_commitish' => $deploy->branch,
            'previous_tag_name' => $deploy->previousVersion->getTag(),
        ]));

        return ['name' => $response['name'], 'body' => $response['body']];
    }

    public function checkSplit(string $sha): void
    {
        $test = $this->api->repository()->workflowRuns()->listRuns(
            username: self::ORG,
            repository: self::REPO,
            workflow: 'splitter.yml',
            parameters: ['head_sha' => $sha]
        );

        $test2 = 1;
    }

    /**
     * @return string[]
     */
    private function getTags(): array
    {
        if (null === $this->tags) {
            $tags = $this->api->repo()->tags(self::ORG, self::REPO);
            $this->tags = \array_map(static fn (array $tag) => $tag['name'], $tags);
        }

        return $this->tags;
    }
}
