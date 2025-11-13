<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Cache;

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
     * @param mixed[] $config
     */
    public function __construct(private readonly RequestStack $requestStack, public readonly array $config)
    {
        $this->resolveConfig($config);
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
     * @param mixed[] $config
     */
    private function resolveConfig(array $config): void
    {
        if (empty($config)) {
            return;
        }
        $resolver = new OptionsResolver();
        $resolver->setRequired(['header']);
        $resolver->addAllowedTypes('header', 'string');
        foreach ($config as $cacheConfig) {
            /** array{header: string} */
            $resolvedConfig = $resolver->resolve($cacheConfig);
            if (null === $this->requestStack->getCurrentRequest()?->headers->get($resolvedConfig['header'])) {
                continue;
            }
            $this->enable = true;

            return;
        }
    }
}
