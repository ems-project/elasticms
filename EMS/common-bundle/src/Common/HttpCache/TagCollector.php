<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\HttpCache;

use Elastica\ResultSet;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagCollector
{
    /** @var array<string, bool> */
    private array $uuids = [];
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

    public function add(ResultSet $resultSet): void
    {
        if (!$this->enable) {
            return;
        }
        foreach ($resultSet->getResults() as $result) {
            $this->uuids[$result->getId()] = true;
        }
    }

    /**
     * @return string[]
     */
    public function all(): array
    {
        return \array_keys($this->uuids);
    }

    public function isEmpty(): bool
    {
        return empty($this->uuids);
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
