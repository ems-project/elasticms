<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Search;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Common\CoreApi\Search\Scroll;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\AdminInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Search\SearchInterface;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\CommonBundle\Elasticsearch\Response\Response;
use EMS\CommonBundle\Elasticsearch\Response\ResponseInterface;
use EMS\CommonBundle\Search\Search as SearchObject;
use EMS\Helpers\Standard\Type;

class Search implements SearchInterface
{
    public function __construct(private readonly Client $client, private readonly AdminInterface $admin)
    {
    }

    #[\Override]
    public function search(SearchObject $search): ResponseInterface
    {
        return Response::fromArray($this->client->post('/api/search/search', ['search' => $search->serialize()])->getData());
    }

    #[\Override]
    public function count(SearchObject $search): int
    {
        $count = $this->client->post('/api/search/count', ['search' => $search->serialize()])->getData()['count'] ?? null;
        if (!\is_int($count)) {
            throw new \RuntimeException('Unexpected: count must be a string');
        }

        return $count;
    }

    #[\Override]
    public function scroll(SearchObject $search, int $scrollSize = 10, string $expireTime = '3m'): Scroll
    {
        $search->setSize($scrollSize);
        $search->setFrom(0);

        return new Scroll($this->client, $search, $expireTime);
    }

    #[\Override]
    public function version(): string
    {
        $version = $this->client->get('/api/search/version')->getData()['version'] ?? null;
        if (!\is_string($version)) {
            throw new \RuntimeException('Unexpected: search must be a string');
        }

        return $version;
    }

    #[\Override]
    public function healthStatus(): string
    {
        $status = $this->client->get('/api/search/health-status')->getData()['status'] ?? null;
        if (!\is_string($status)) {
            throw new \RuntimeException('Unexpected: status must be a string');
        }

        return $status;
    }

    #[\Override]
    public function refresh(?string $index = null): bool
    {
        $success = $this->client->post('/api/search/refresh', [
            'index' => $index,
        ])->getData()['success'] ?? null;
        if (!\is_bool($success)) {
            throw new \RuntimeException('Unexpected: search must be a boolean');
        }

        return $success;
    }

    /**
     * @param  string[] $aliases
     * @return string[]
     */
    public function getIndicesFromAliases(array $aliases): array
    {
        if (\version_compare($this->admin->getCoreVersion(), '5.13.5') <= 0) {
            $indices = [];
            foreach ($aliases as $alias) {
                $indices = \array_merge($indices, $this->getIndicesFromAlias($alias));
            }

            return \array_unique($indices);
        }

        $indices = $this->client->post('/api/search/indices-from-aliases', [
            'aliases' => $aliases,
        ])->getData()['indices'] ?? null;
        if (!\is_array($indices)) {
            throw new \RuntimeException('Unexpected: indices must be an array');
        }

        return $indices;
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function getIndicesFromAlias(string $alias): array
    {
        $indices = $this->client->post('/api/search/indices-from-alias', [
            'alias' => $alias,
        ])->getData()['indices'] ?? null;
        if (!\is_array($indices)) {
            throw new \RuntimeException('Unexpected: indices must be an array');
        }

        return $indices;
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function getAliasesFromIndex(string $index): array
    {
        $aliases = $this->client->post('/api/search/aliases-from-index', [
            'index' => $index,
        ])->getData()['aliases'] ?? null;
        if (!\is_array($aliases)) {
            throw new \RuntimeException('Unexpected: aliases must be an array');
        }

        return $aliases;
    }

    /**
     * @param string[] $sourceIncludes
     * @param string[] $sourcesExcludes
     */
    #[\Override]
    public function getDocument(string $index, ?string $contentType, string $id, array $sourceIncludes = [], array $sourcesExcludes = []): DocumentInterface
    {
        return Document::fromArray($this->client->post('/api/search/document', [
            'index' => $index,
            'content-type' => $contentType,
            'ouuid' => $id,
            'source-includes' => $sourceIncludes,
            'sources-excludes' => $sourcesExcludes,
        ])->getData());
    }

    /**
     * @param string[] $aliases
     *
     * @return array<string, array<int, string>>
     */
    #[\Override]
    public function getIndicesForContentTypes(array $aliases): array
    {
        $indices = $this->client->post('/api/search/indices-for-content-type', [
            'aliases' => $aliases,
        ])->getData()['indices'] ?? null;
        if (!\is_array($indices)) {
            throw new \RuntimeException('Unexpected: search must be an array');
        }

        return $indices;
    }

    /**
     * @param string[] $words
     *
     * @return string[]
     */
    #[\Override]
    public function filterStopWords(string $index, string $analyzer, array $words): array
    {
        $filtered = $this->client->post('/api/search/filter-stop-words', [
            'index' => $index,
            'analyzer' => $analyzer,
            'words' => $words,
        ])->getData()['filtered'] ?? null;
        if (!\is_array($filtered)) {
            throw new \RuntimeException('Unexpected: filtered must be an array');
        }

        return $filtered;
    }

    /**
     * @param array<string, string|string[]> $parameters
     *
     * @return array<int, array<string, int|string>>
     */
    public function analyze(string $text, array $parameters, ?string $index): array
    {
        $tokens = $this->client->post('/api/search/analyze', [
            'index' => $index,
            'text' => $text,
            'parameters' => $parameters,
        ])->getData()['tokens'] ?? null;
        if (!\is_array($tokens)) {
            throw new \RuntimeException('Unexpected: filtered must be an array');
        }

        return $tokens;
    }

    #[\Override]
    public function hasIndex(string $index): bool
    {
        $response = $this->client->post('/api/search/has-index', [
            'index' => $index,
        ])->getData()['exist'] ?? null;

        return Type::bool($response);
    }
}
