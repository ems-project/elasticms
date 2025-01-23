<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Elasticsearch;

use Elastica\Aggregation\Terms;
use Elastica\Query\AbstractQuery;
use Elastica\ResultSet;
use EMS\ClientHelperBundle\Contracts\Elasticsearch\ClientRequestInterface;
use EMS\ClientHelperBundle\Exception\SingleResultException;
use EMS\ClientHelperBundle\Helper\Cache\CacheHelper;
use EMS\ClientHelperBundle\Helper\ContentType\ContentType;
use EMS\ClientHelperBundle\Helper\ContentType\ContentTypeHelper;
use EMS\ClientHelperBundle\Helper\Environment\Environment;
use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Elasticsearch\Document\EMSSource;
use EMS\CommonBundle\Elasticsearch\Exception\NotFoundException;
use EMS\CommonBundle\Search\Search;
use EMS\CommonBundle\Service\ElasticaService;
use EMS\Helpers\Standard\Hash;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class ClientRequest implements ClientRequestInterface
{
    private const int CONTENT_TYPE_LIMIT = 500;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(private readonly ElasticaService $elasticaService, private readonly EnvironmentHelper $environmentHelper, private readonly CacheHelper $cacheHelper, private readonly ContentTypeHelper $contentTypeHelper, private readonly LoggerInterface $logger, private readonly CacheItemPoolInterface $cache, private readonly string $name, private array $options = [])
    {
    }

    public function getUrl(): string
    {
        return $this->elasticaService->getUrl();
    }

    /**
     * @return string[]
     */
    public function analyze(string $text, string $analyzer): array
    {
        if (empty($text)) {
            return [];
        }

        $this->logger->debug('ClientRequest : analyze {text} with {analyzer}', ['text' => $text, 'analyzer' => $analyzer]);
        $out = [];
        \preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $text, $out);
        $words = $out[0];

        foreach ($this->elasticaService->getIndicesFromAlias($this->getAlias()) as $index) {
            try {
                return $this->elasticaService->filterStopWords($index, $analyzer, $words);
            } catch (\Throwable) {
            }
        }

        throw new \RuntimeException(\sprintf('Analyzer %s not found', $analyzer));
    }

    /**
     * @return array{_id: string, _type?: string, _source: array<mixed>}
     */
    #[\Override]
    public function get(string $type, string $id): array
    {
        $this->logger->debug('ClientRequest : get {type}:{id}', ['type' => $type, 'id' => $id]);

        return $this->searchOne($type, [
            'query' => [
                'term' => [
                    '_id' => $id,
                ],
            ],
        ]);
    }

    /**
     * @return string[]
     */
    public function getAllChildren(string $emsKey, string $childrenField): array
    {
        $this->logger->debug('ClientRequest : getAllChildren for {emsKey}', ['emsKey' => $emsKey]);
        $out = [$emsKey];
        $item = $this->getByEmsKey($emsKey);

        if (false === $item) {
            return $out;
        }

        if (isset($item['_source'][$childrenField]) && \is_array($item['_source'][$childrenField])) {
            foreach ($item['_source'][$childrenField] as $key) {
                $out = \array_merge($out, $this->getAllChildren($key, $childrenField));
            }
        }

        return $out;
    }

    /**
     * @param string[] $sourceFields
     *
     * @return array<string, mixed>|false
     */
    #[\Override]
    public function getByEmsKey(string $emsLink, array $sourceFields = []): array|false
    {
        $type = ClientRequest::getType($emsLink);
        if (null === $type) {
            throw new \RuntimeException('Unexpected null type');
        }
        $ouuid = ClientRequest::getOuuid($emsLink);
        if (null === $ouuid) {
            throw new \RuntimeException('Unexpected null ouuid');
        }

        return $this->getByOuuid($type, $ouuid, $sourceFields);
    }

    /**
     * @param string[] $sourceFields
     * @param string[] $sourceExclude
     *
     * @return array<string, mixed>|false
     */
    public function getByOuuid(string $type, string $ouuid, array $sourceFields = [], array $sourceExclude = []): array|false
    {
        $this->logger->debug('ClientRequest : getByOuuid {type}:{id}', ['type' => $type, 'id' => $ouuid]);

        foreach ($this->elasticaService->getIndicesFromAlias($this->getAlias()) as $index) {
            try {
                $document = $this->elasticaService->getDocument($index, $type, $ouuid, $sourceFields, $sourceExclude);

                return $document->getRaw();
            } catch (NotFoundException) {
            }
        }

        return false;
    }

    /**
     * @param list<string> $ouuids
     *
     * @return array<mixed>
     */
    #[\Override]
    public function getByOuuids(string $type, array $ouuids): array
    {
        $this->logger->debug('ClientRequest : getByOuuids {type}:{id}', ['type' => $type, 'id' => $ouuids]);

        $query = $this->elasticaService->getTermsQuery('_id', $ouuids);
        $query = $this->elasticaService->filterByContentTypes($query, [$type]);
        $search = new Search([$this->getAlias()], $query);
        $search->setContentTypes([$type]);
        $search->setSize(\count($ouuids));

        return $this->elasticaService->search($search)->getResponse()->getData();
    }

    /**
     * @return string[]
     */
    public function getContentTypes(): array
    {
        $search = new Search([$this->getAlias()]);
        $search->setSize(0);
        $terms = new Terms(EMSSource::FIELD_CONTENT_TYPE);
        $terms->setField(EMSSource::FIELD_CONTENT_TYPE);
        $terms->setSize(self::CONTENT_TYPE_LIMIT);
        $search->addAggregation($terms);
        $resultSet = $this->elasticaService->search($search);
        $aggregation = $resultSet->getAggregation(EMSSource::FIELD_CONTENT_TYPE);
        $contentTypes = [];
        foreach ($aggregation['buckets'] ?? [] as $bucket) {
            $contentTypes[] = $bucket['key'];
        }
        if (\count($contentTypes) >= self::CONTENT_TYPE_LIMIT) {
            $this->logger->warning('The get content type function is only considering the first {limit} content type', ['limit' => self::CONTENT_TYPE_LIMIT]);
        }

        return $contentTypes;
    }

    public function getFieldAnalyzer(string $field): string
    {
        $this->logger->debug('ClientRequest : getFieldAnalyzer {field}', ['field' => $field]);

        foreach ($this->elasticaService->getIndicesFromAlias($this->getAlias()) as $index) {
            try {
                return $this->elasticaService->getFieldAnalyzer($index, $field);
            } catch (\Throwable) {
            }
        }

        throw new \RuntimeException(\sprintf('Field analyzer %s not found', $field));
    }

    /**
     * @param string[] $sourceFields
     * @param mixed[]  $cache
     */
    public function getHierarchy(string $emsKey, string $childrenField, ?int $depth = null, array $sourceFields = [], ?EMSLink $activeChild = null, int $querySize = 1000, array $cache = []): ?HierarchicalStructure
    {
        $this->logger->debug('ClientRequest : getHierarchy for {emsKey}', ['emsKey' => $emsKey]);
        $emsLink = EMSLink::fromText($emsKey);
        $items = $cache;
        if (empty($items)) {
            $result = $this->search($emsLink->getContentType(), [
                '_source' => $sourceFields,
            ], 0, $querySize);

            $ids = [];
            foreach ($result['hits']['hits'] ?? [] as $document) {
                $items[\sprintf('%s:%s', $emsLink->getContentType(), $document['_id'])] = $document;
                $ids = \array_merge($ids, \array_filter(\array_map(fn (string $a): EMSLink => EMSLink::fromText($a), $document['_source'][$childrenField] ?? []), fn (EMSLink $a): bool => $emsLink->getContentType() !== $a->getContentType()));
            }
            $contentTypes = \array_keys(\array_reduce($ids, function (array $carry, $l): array {
                $carry[$l->getContentType()] = $l->getContentType();

                return $carry;
            }, []));
            $result = $this->search($contentTypes, [
                '_source' => $sourceFields,
                'query' => [
                    'terms' => [
                        '_id' => \array_map(fn (EMSLink $l): string => $l->getOuuid(), $ids),
                    ],
                ],
            ], 0, \count($ids));
            foreach ($result['hits']['hits'] ?? [] as $document) {
                $items[\sprintf('%s:%s', $document['_source'][EMSSource::FIELD_CONTENT_TYPE], $document['_id'])] = $document;
            }
        }

        $item = $items[$emsKey] ?? null;

        if (null === $item) {
            return null;
        }
        $contentType = $item['_source'][EMSSource::FIELD_CONTENT_TYPE] ?? null;
        if (null === $contentType) {
            throw new \RuntimeException('Unexpected not defined content type');
        }
        $out = new HierarchicalStructure($contentType, $item['_id'], $item['_source'], $activeChild);

        if (null === $depth || $depth) {
            if (isset($item['_source'][$childrenField]) && \is_array($item['_source'][$childrenField])) {
                foreach ($item['_source'][$childrenField] as $key) {
                    if ($key) {
                        $child = $this->getHierarchy($key, $childrenField, null === $depth ? null : $depth - 1, $sourceFields, $activeChild, $querySize, $items);
                        if ($child) {
                            $out->addChild($child);
                        }
                    }
                }
            }
        }

        return $out;
    }

    public function getCurrentEnvironment(): ?Environment
    {
        return $this->environmentHelper->getCurrentEnvironment();
    }

    public function cacheContentType(ContentType $contentType): void
    {
        $this->cacheHelper->saveContentType($contentType);
    }

    public function getSettings(Environment $environment, bool $cache = true): Settings
    {
        static $save = [];

        if (isset($save[$environment->getName()]) && $cache) {
            return $save[$environment->getName()];
        }

        $settings = new Settings();

        if (null !== $routeContentTypeName = $this->getOption('[route_type]')) {
            $settings->addRouting($routeContentTypeName, $this->getContentType($routeContentTypeName, $environment));
        }

        if (null !== $translationContentTypeName = $this->getOption('[translation_type]')) {
            $translationContentType = $this->getContentType($translationContentTypeName, $environment);
            $settings->addTranslation($translationContentTypeName, $translationContentType);
        }

        if (null !== $templates = $this->getOption('[templates]')) {
            foreach ($templates as $templateContentTypeName => $templateMapping) {
                $templateContentType = $this->getContentType($templateContentTypeName, $environment);
                $settings->addTemplating($templateContentTypeName, $templateMapping, $templateContentType);
            }
        }

        $save[$environment->getName()] = $settings;

        return $settings;
    }

    #[\Override]
    public function getContentType(string $name, ?Environment $environment = null): ?ContentType
    {
        if (null === $environment) {
            if (null === $currentEnvironment = $this->getCurrentEnvironment()) {
                return null;
            }
            $environment = $currentEnvironment;
        }

        if (null === $contentType = $this->contentTypeHelper->get($this, $environment, $name)) {
            return null;
        }

        $cachedContentType = $this->cacheHelper->getContentType($contentType);

        return $cachedContentType ?: $contentType;
    }

    public function getLocale(): string
    {
        return $this->environmentHelper->getLocale();
    }

    public static function getOuuid(string $emsLink): ?string
    {
        if (!\strpos($emsLink, ':')) {
            return $emsLink;
        }

        $split = \preg_split('/:/', $emsLink);
        if (!\is_array($split)) {
            throw new \RuntimeException(\sprintf('Unexpected not support emslink format : %s', $emsLink));
        }
        $ouuid = \end($split);

        if (false === $ouuid) {
            return null;
        }

        return $ouuid;
    }

    public function hasOption(string $option): bool
    {
        return isset($this->options[$option]) && null != $this->options[$option];
    }

    public function refresh(): bool
    {
        return $this->elasticaService->refresh($this->getAlias());
    }

    public function healthStatus(): string
    {
        return $this->elasticaService->getHealthStatus();
    }

    /**
     * @param mixed $default
     *
     * @return mixed|null
     */
    #[\Override]
    public function getOption(string $propertyPath, $default = null)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if (!$propertyAccessor->isReadable($this->options, $propertyPath)) {
            return $default;
        }

        return $propertyAccessor->getValue($this->options, $propertyPath);
    }

    public static function getType(string $emsLink): ?string
    {
        if (!\strpos($emsLink, ':')) {
            return $emsLink;
        }

        $split = \preg_split('/:/', $emsLink);

        if (\is_array($split) && \is_string($split[0] ?? null)) {
            return $split[0];
        }

        return null;
    }

    /**
     * @param string|string[]|null $type
     * @param array<mixed>         $body
     * @param string[]             $sourceExclude
     *
     * @return array<mixed>
     */
    public function search(string|array|null $type, array $body, int $from = 0, int $size = 10, array $sourceExclude = [], ?string $regex = null, ?string $index = null)
    {
        if (null === $type) {
            $types = [];
        } elseif (\is_array($type)) {
            $types = $type;
        } else {
            $types = \explode(',', $type);
        }

        if (null === $index) {
            $index = $this->getAlias();
        }

        $arguments = [
            'index' => $index,
            'type' => $type,
            'body' => $body,
            'size' => $body['size'] ?? $size,
            'from' => $body['from'] ?? $from,
        ];

        if (!empty($sourceExclude)) {
            @\trigger_error('_source_exclude field are not supported anymore', E_USER_DEPRECATED);
        }

        $this->logger->debug('ClientRequest : search for {type}', $arguments);
        $search = $this->elasticaService->convertElasticsearchSearch($arguments);
        $search->setContentTypes($types);
        $search->setRegex($regex);
        $resultSet = $this->elasticaService->search($search);

        return $resultSet->getResponse()->getData();
    }

    /**
     * @param list<string> $types
     */
    public function initializeCommonSearch(array $types, ?AbstractQuery $query = null): Search
    {
        $query = $this->elasticaService->filterByContentTypes($query, $types);

        return new Search([$this->getAlias()], $query);
    }

    public function commonSearch(Search $search): ResultSet
    {
        return $this->elasticaService->search($search);
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @return array<mixed>
     */
    public function searchArgs(array $arguments): array
    {
        if (!isset($arguments['index'])) {
            $arguments['index'] = $this->getAlias();
        }
        $search = $this->elasticaService->convertElasticsearchSearch($arguments);

        return $this->elasticaService->search($search)->getResponse()->getData();
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<mixed>
     */
    public function searchBy(string $type, array $parameters, int $from = 0, int $size = 10): array
    {
        $this->logger->debug('ClientRequest : searchBy for type {type}', ['type' => $type]);
        $body = [
            'query' => [
                'bool' => [
                    'must' => [],
                ],
            ],
        ];

        foreach ($parameters as $id => $value) {
            $body['query']['bool']['must'][] = [
                'term' => [
                    $id => [
                        'value' => $value,
                    ],
                ],
            ];
        }

        $search = $this->elasticaService->convertElasticsearchSearch([
            'index' => $this->getAlias(),
            'type' => $type,
            'body' => $body,
            'size' => $size,
            'from' => $from,
        ]);

        return $this->elasticaService->search($search)->getResponse()->getData();
    }

    /**
     * @param string|string[]      $type
     * @param array<string, mixed> $body
     *
     * @return array{_id: string, _type?: string, _source: array<mixed>}
     */
    public function searchOne(string|array|null $type, array $body, ?string $indexRegex = null): array
    {
        $this->logger->debug('ClientRequest : searchOne for {type}', ['type' => $type, 'body' => $body, 'indexRegex' => $indexRegex]);
        $search = $this->search($type, $body, 0, 2, [], $indexRegex);

        $hits = $search['hits'];

        if (1 !== (\is_countable($hits['hits']) ? \count($hits['hits']) : 0)) {
            throw new SingleResultException(\sprintf('expected 1 result, got %d', $hits['hits']));
        }

        return $hits['hits'][0];
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array{_id:string,_type:string,_source:array<mixed>}|null
     */
    public function searchOneBy(string $type, array $parameters): ?array
    {
        $this->logger->debug('ClientRequest : searchOneBy for type {type}', ['type' => $type]);

        $result = $this->searchBy($type, $parameters, 0, 1);

        if (1 == $result['hits']['total']) {
            return $result['hits']['hits'][0];
        }

        return null;
    }

    /**
     * @param string[] $filter
     *
     * @return array<mixed>
     */
    public function scroll(string $type, array $filter = [], int $size = 10, ?string $scrollId = null): array
    {
        $scrollTimeout = '30s';

        if ($scrollId) {
            return $this->elasticaService->nextScroll($scrollId, $scrollTimeout)->getData();
        }

        $search = $this->elasticaService->convertElasticsearchSearch([
            'index' => $this->getAlias(),
            'type' => $type,
            '_source' => $filter,
            'size' => $size,
        ]);

        return $this->elasticaService->scrollById($search, $scrollTimeout)->getResponse()->getData();
    }

    /**
     * @param array<mixed> $params
     *
     * @return \Generator<array<mixed>>
     */
    public function scrollAll(array $params, string $timeout = '30s', ?string $index = null): iterable
    {
        if (null === $index) {
            $index = $this->getAlias();
        }
        $params['index'] = $index;
        $search = $this->elasticaService->convertElasticsearchSearch($params);
        $scroll = $this->elasticaService->scroll($search, $timeout);

        foreach ($scroll as $resultSet) {
            foreach ($resultSet as $result) {
                yield $result->getHit();
            }
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    #[\Override]
    public function getCacheKey(string $prefix = '', ?string $environment = null): string
    {
        if ($environment) {
            return $prefix.$environment;
        }

        if (null === $currentEnvironment = $this->getCurrentEnvironment()) {
            throw new \RuntimeException('No active environment');
        }

        return $prefix.$currentEnvironment->getName();
    }

    public function getAlias(): string
    {
        if (null === $currentEnvironment = $this->getCurrentEnvironment()) {
            throw new \RuntimeException('No current environment found!');
        }

        return $currentEnvironment->getAlias();
    }

    /**
     * @param array<mixed> $cacheKey
     */
    public function getCacheResponse(array $cacheKey, ?string $type, callable $function): Response
    {
        if (null === $type) {
            return $function();
        }

        $cacheHash = Hash::array($cacheKey);
        $cachedHierarchy = $this->cache->getItem($cacheHash);

        /** @var Response|null $response */
        $response = $cachedHierarchy->get();

        $lastPublishedDate = $this->getLastPublishedDate($type);
        $lastModified = $response ? $response->getLastModified() : null;
        $isModified = !$lastModified || $lastModified->getTimestamp() !== $lastPublishedDate->getTimestamp();

        if (!$cachedHierarchy->isHit() || $isModified) {
            $response = $function();
            $response->setLastModified($lastPublishedDate);
            $this->cache->save($cachedHierarchy->set($response));
            $this->logger->info('log.cache_missed', [
                'cache_key' => $cacheHash,
                'type' => $type,
            ]);
        } else {
            $this->logger->info('log.cache_hit', [
                'cache_key' => $cacheHash,
                'type' => $type,
            ]);
            $response = $cachedHierarchy->get();
        }

        return $response;
    }

    /**
     * @param mixed[] $config
     */
    public function addEnvironment(string $name, array $config = [], bool $overwrite = false): void
    {
        if (!$overwrite && null !== $this->environmentHelper->getEnvironment($name)) {
            return;
        }
        $this->environmentHelper->addEnvironment($name, $config);
        $this->environmentHelper->giveEnvironment($name)->makeActive();
    }

    /**
     * @param AbstractQuery|array<mixed>|null $query
     */
    public function getCommonSearch(array|AbstractQuery|null $query = null): Search
    {
        return new Search([$this->getAlias()], $query);
    }

    private function getLastPublishedDate(string $contentTypeNames): \DateTimeImmutable
    {
        $publishDates = [];

        foreach (\explode(',', $contentTypeNames) as $contentTypeName) {
            $contentType = $this->getContentType($contentTypeName);
            $publishDates[] = $contentType ? $contentType->getLastPublished() : null;
        }

        $lastPublishedDate = \max($publishDates);

        if ($lastPublishedDate instanceof \DateTimeImmutable) {
            return $lastPublishedDate;
        }

        return new \DateTimeImmutable('Wed, 09 Feb 1977 16:00:00 GMT');
    }
}
