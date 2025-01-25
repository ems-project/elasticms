<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi;

use EMS\Helpers\Standard\Hash;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class TokenStore
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly ?string $defaultBaseUrl,
        private readonly ?string $defaultApiToken,
    ) {
    }

    public function saveToken(string $baseUrl, string $token): void
    {
        $this->cache->save($this->apiCacheBaseUrl()->set($baseUrl));
        $this->cache->save($this->apiCacheToken($baseUrl)->set($token));
    }

    public function getBaseUrl(): ?string
    {
        $cacheBaseUrl = $this->apiCacheBaseUrl();
        $baseUrl = $cacheBaseUrl->get();
        if (\is_string($baseUrl)) {
            return $baseUrl;
        }
        if (null !== $this->defaultBaseUrl) {
            $this->cache->save($cacheBaseUrl->set($this->defaultBaseUrl));
            $this->cache->save($this->apiCacheToken($this->defaultBaseUrl)->set($this->defaultApiToken));
        }

        return $this->defaultBaseUrl;
    }

    public function giveBaseUrl(): string
    {
        $baseUrl = $this->getBaseUrl();
        if (null === $baseUrl) {
            throw new \RuntimeException('Not authenticated, run ems:admin:login or ems:local:login');
        }

        return $baseUrl;
    }

    public function getToken(?string $baseUrl = null): ?string
    {
        if (null !== $baseUrl) {
            $cacheBaseUrl = $this->apiCacheBaseUrl();
            if ($cacheBaseUrl->get() !== $baseUrl) {
                $this->cache->save($cacheBaseUrl->set($baseUrl));
            }
        } else {
            $baseUrl = $this->giveBaseUrl();
        }
        $cacheToken = $this->apiCacheToken($baseUrl);
        $token = $cacheToken->get();
        if (\is_string($token) && $cacheToken->isHit()) {
            return $token;
        }

        return null;
    }

    public function giveToken(?string $baseUrl = null): string
    {
        $token = $this->getToken($baseUrl);
        if (null === $token) {
            throw new \RuntimeException('Not authenticated, run ems:admin:login or ems:local:login');
        }

        return $token;
    }

    private function apiCacheBaseUrl(): CacheItemInterface
    {
        return $this->cache->getItem('ems_admin_base_url');
    }

    private function apiCacheToken(string $baseUrl): CacheItemInterface
    {
        return $this->cache->getItem(Hash::string($baseUrl, 'token_'));
    }
}
