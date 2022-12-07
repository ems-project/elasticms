<?php

namespace EMS\CommonBundle\Common\Admin;

use EMS\CommonBundle\Common\Standard\Hash;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiFactoryInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class AdminHelper
{
    private ?CoreApiInterface $coreApi = null;

    public function __construct(private readonly CoreApiFactoryInterface $coreApiFactory, private readonly CacheItemPoolInterface $cache, private readonly LoggerInterface $logger)
    {
    }

    public function login(string $baseUrl, string $username, string $password): CoreApiInterface
    {
        $this->coreApi = $this->coreApiFactory->create($baseUrl);
        $this->coreApi->authenticate($username, $password);
        $this->coreApi->setLogger($this->logger);
        $this->cache->save($this->apiCacheBaseUrl()->set($this->coreApi->getBaseUrl()));
        $this->cache->save($this->apiCacheToken($this->coreApi)->set($this->coreApi->getToken()));

        return $this->coreApi;
    }

    private function apiCacheBaseUrl(): CacheItemInterface
    {
        return $this->cache->getItem('ems_admin_base_url');
    }

    private function apiCacheToken(CoreApiInterface $coreApi): CacheItemInterface
    {
        return $this->cache->getItem(Hash::string($coreApi->getBaseUrl(), 'token_'));
    }

    public function getCoreApi(): CoreApiInterface
    {
        if (null !== $this->coreApi) {
            return $this->coreApi;
        }
        $baseUrl = $this->apiCacheBaseUrl()->get();
        if (!\is_string($baseUrl)) {
            throw new \RuntimeException('Not authenticated, run ems:admin:login');
        }
        $this->coreApi = $this->coreApiFactory->create($baseUrl);
        $this->coreApi->setLogger($this->logger);
        $this->coreApi->setToken($this->apiCacheToken($this->coreApi)->get());

        return $this->coreApi;
    }
}
