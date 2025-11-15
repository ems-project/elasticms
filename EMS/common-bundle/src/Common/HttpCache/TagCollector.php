<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\HttpCache;

use Elastica\ResultSet;
use EMS\CommonBundle\Elasticsearch\Document\EMSSource;
use EMS\CommonBundle\Search\Search;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagCollector
{
    /** @var array<string, bool> */
    private array $uuids = [];
    /** @var array<string, bool> */
    private array $indices = [];
    /** @var array<string, bool> */
    private array $contentTypes = [];
    private bool $enable = false;

    /**
     * @param mixed[] $jsonConfig
     */
    public function __construct(private readonly RequestStack $requestStack, private readonly array $jsonConfig)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }
        foreach ($this->cacheConfigs() as $cacheConfigs) {
            if (null === $request->headers->get($cacheConfigs->header)) {
                continue;
            }
            $this->enable = true;

            return;
        }
    }

    public function add(Search $search, ResultSet $resultSet): void
    {
        if (!$this->enable) {
            return;
        }
        foreach ($search->getIndices() as $index) {
            $this->indices[$index] = true;
        }
        foreach ($resultSet->getResults() as $result) {
            $this->uuids[$result->getId()] = true;
            $contentType = $result->getSource()[EMSSource::FIELD_CONTENT_TYPE] ?? null;
            if (null !== $contentType) {
                $this->contentTypes[$contentType] = true;
            }
        }
    }

    /**
     * @return string[]
     */
    public function all(): array
    {
        return \array_keys(\array_merge($this->uuids, $this->indices, $this->contentTypes));
    }

    public function isEmpty(): bool
    {
        return empty($this->uuids) && empty($this->indices) && empty($this->contentTypes);
    }

    /**
     * @return \Generator<HttpCacheConfig>
     */
    public function cacheConfigs(): iterable
    {
        if (empty($this->jsonConfig)) {
            return;
        }
        $resolver = new OptionsResolver();
        $resolver->setRequired(['header', 'url', 'headers', 'verify_ssl']);
        $resolver->addAllowedTypes('header', 'string');
        $resolver->addAllowedTypes('url', 'string');
        $resolver->addAllowedTypes('headers', 'array');
        $resolver->addAllowedTypes('verify_ssl', 'bool');
        $resolver->setDefault('headers', []);
        $resolver->setDefault('verify_ssl', true);
        foreach ($this->jsonConfig as $cacheConfig) {
            /** array{header: string, url: string, array: string[]|string[][], verify_ssl: bool} */
            $resolvedConfig = $resolver->resolve($cacheConfig);
            yield new HttpCacheConfig(
                $resolvedConfig['header'],
                $resolvedConfig['url'],
                $resolvedConfig['headers'],
                $resolvedConfig['verify_ssl'],
            );
        }
    }
}
