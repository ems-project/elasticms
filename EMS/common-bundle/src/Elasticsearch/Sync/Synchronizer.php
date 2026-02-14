<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Sync;

use Elastica\Query;
use EMS\Helpers\Html\Headers;
use EMS\Helpers\Html\MimeTypes;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Synchronizer
{
    public const string ID = '_id';
    private HttpClientInterface $client;
    private bool $defined;
    private readonly string $alias;
    private ?string $index = null;
    private ?string $previousIndex = null;
    /** @var mixed[] */
    private array $mappings;
    /** @var mixed[] */
    private $settings;

    /**
     * @param array<mixed> $headers
     */
    private function __construct(
        HttpClientInterface $httpClient,
        public readonly string $baseUrl,
        public readonly array $headers = []
    ) {
        if (\str_ends_with($baseUrl, '/')) {
            throw new \RuntimeException('The baseurl cannot end with a slash');
        }

        $this->client = $httpClient->withOptions([
            'base_uri' => $baseUrl.'/',
            'headers' => \array_merge($headers, [
                Headers::CONTENT_TYPE => MimeTypes::APPLICATION_JSON->value,
            ]),
        ]);

        $path = \explode('/', $baseUrl);
        $this->alias = Type::string(\end($path));
    }

    /**
     * @param array<mixed> $headers
     */
    public static function create(HttpClientInterface $httpClient, string $baseUrl, array $headers = []): self
    {
        $indexClient = new self($httpClient, $baseUrl, $headers);
        try {
            $response = Json::decode($indexClient->client->request('GET', '')->getContent());
            $indexClient->defined = true;
        } catch (ClientException $e) {
            if (404 !== $e->getCode()) {
                throw $e;
            }
            $indexClient->defined = false;

            return $indexClient;
        }
        if (1 !== \count($response)) {
            throw new \RuntimeException(\sprintf('SimpleIndexClient does not support multiple indexes alias. This alias contains %d indexes', \count($response)));
        }
        $indexClient->previousIndex = Type::string(\array_key_first($response));
        $indexClient->index = $indexClient->previousIndex;
        $indexClient->mappings = $response[$indexClient->index]['mappings'];
        $indexClient->settings = $response[$indexClient->index]['settings']['index'];
        $indexClient->settings = \array_filter($indexClient->settings, fn ($v, $k) => 'analysis' === $k, ARRAY_FILTER_USE_BOTH);

        return $indexClient;
    }

    public function isDefined(): bool
    {
        return $this->defined;
    }

    public function getIndex(): string
    {
        return Type::string($this->index);
    }

    /**
     * @return array<mixed>
     */
    public function getMappings(): array
    {
        return $this->mappings;
    }

    /**
     * @param mixed[] $sourceMapping
     * @param mixed[] $metas
     */
    public function updateMapping(array $sourceMapping, array $metas): bool
    {
        try {
            $sourceMapping['_meta'] = $metas;
            $response = Json::decode($this->client->request('PUT', '_mapping', [
                'body' => Json::encode($sourceMapping),
            ])->getContent());

            return true === ($response['acknowledged'] ?? null);
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * @param mixed[] $sourceMappings
     * @param mixed[] $settings
     * @param mixed[] $metas
     */
    public function createIndex(HttpClientInterface $httpClient, array $sourceMappings, array $settings, array $metas): void
    {
        $client = $httpClient->withOptions([
            'base_uri' => \sprintf('%s_%s/', $this->baseUrl, new \DateTime()->format('Ymd_His')),
            'headers' => \array_merge($this->headers, [
                Headers::CONTENT_TYPE => MimeTypes::APPLICATION_JSON->value,
            ]),
        ]);
        $sourceMappings['_meta'] = $metas;
        $body = ['mappings' => $sourceMappings];
        if (\count($settings) > 0) {
            $body['settings'] = $settings;
        }

        $response = Json::decode($client->request('PUT', '', [
            'body' => Json::encode($body),
        ])->getContent());
        if (true !== ($response['acknowledged'] ?? null)) {
            throw new \RuntimeException('Impossible to create a new index');
        }
        $this->defined = true;
        $this->client = $client;
        $this->index = Type::string($response['index']);
    }

    /**
     * @return mixed[]
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    public function switchAlias(): void
    {
        if ($this->previousIndex === $this->index) {
            return;
        }
        if (null === $this->index) {
            throw new \RuntimeException('The index alias is not defined');
        }
        $actions = [[
            'add' => [
                'index' => $this->index,
                'alias' => $this->alias,
            ],
        ]];
        if (null !== $this->previousIndex) {
            $actions[] = [
                'remove' => [
                    'index' => $this->previousIndex,
                    'alias' => $this->alias,
                ],
            ];
        }

        $response = Json::decode($this->client->request('POST', '../_aliases', [
            'body' => Json::encode([
                'actions' => $actions,
            ]),
        ])->getContent());
        if (true !== ($response['acknowledged'] ?? null)) {
            throw new \RuntimeException('Impossible to switch alias');
        }
    }

    public function search(Query $query, ?string $scroll = null): SearchResponse
    {
        $params = [];
        if (null !== $scroll) {
            $params['scroll'] = $scroll;
            $query->setSort(['_doc']);
        }
        $response = Json::decode($this->client->request('GET', '_search?'.\http_build_query($params), [
            'body' => Json::encode($query->toArray()),
        ])->getContent());

        return new SearchResponse($response);
    }

    public function scroll(string $scrollId, string $scroll): SearchResponse
    {
        $response = Json::decode($this->client->request('GET', '../_search/scroll', [
            'body' => Json::encode([
                'scroll_id' => $scrollId,
                'scroll' => $scroll,
            ]),
        ])->getContent());

        return new SearchResponse($response);
    }

    /**
     * @param mixed[] $source
     */
    public function index(string $id, array $source): void
    {
        $this->client->request('POST', '_doc/'.$id, [
            'body' => Json::encode($source),
        ])->getContent();
    }

    /**
     * @return array<string, int>
     */
    public function bulk(BulkRequest $bulk): array
    {
        $response = Json::decode($this->client->request('POST', '_bulk', [
            'body' => $bulk->getBody(),
        ])->getContent());
        $status = [];
        foreach ($response['items'] as $item) {
            $action = \array_key_first($item);
            $status[$item[$action]['_id']] = $item[$action]['status'];
        }

        return $status;
    }

    public function closeScroll(string $scrollId): void
    {
        try {
            $this->client->request('DELETE', '../_search/scroll', [
                'json' => [
                    'scroll_id' => $scrollId,
                ],
            ]);
        } catch (ClientExceptionInterface $e) {
            if (404 === $e->getCode()) {
                return;
            }
            throw $e;
        }
    }
}
