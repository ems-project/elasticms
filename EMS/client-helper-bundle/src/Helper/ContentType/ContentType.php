<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\ContentType;

use EMS\ClientHelperBundle\Contracts\ContentType\ContentTypeInterface;
use EMS\ClientHelperBundle\Helper\Environment\Environment;

final class ContentType implements ContentTypeInterface
{
    private Environment $environment;
    private string $name;
    private \DateTimeImmutable $lastPublished;
    private int $total;
    /** @var mixed */
    private $cache = null;

    public function __construct(Environment $environment, string $name, int $total)
    {
        $this->environment = $environment;
        $this->name = $name;
        $this->total = $total;
        $this->lastPublished = new \DateTimeImmutable();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function isLastPublishedAfterTime(int $timestamp): bool
    {
        return $this->lastPublished->getTimestamp() <= $timestamp;
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheValidityTag(): string
    {
        return \sprintf('%d_%d', $this->getLastPublished()->getTimestamp(), $this->total);
    }

    public function getCacheKey(): string
    {
        return \sprintf('%s_%s', $this->environment->getAliasForCacheKey(), $this->name);
    }

    /**
     * @return mixed
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param mixed $cache
     */
    public function setCache($cache): void
    {
        $this->cache = $cache;
    }

    public function getLastPublished(): \DateTimeImmutable
    {
        return $this->lastPublished;
    }

    public function setLastPublishedValue(string $lastPublishedValue): void
    {
        $lastPublished = \DateTimeImmutable::createFromFormat(\DATE_ATOM, $lastPublishedValue);

        if ($lastPublished) {
            $this->lastPublished = $lastPublished;
        }
    }
}
