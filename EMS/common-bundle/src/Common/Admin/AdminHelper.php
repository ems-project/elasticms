<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Admin;

use EMS\CommonBundle\Common\CoreApi\TokenStore;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\CoreApi\Exception\BaseUrlNotDefinedExceptionInterface;
use Psr\Log\LoggerInterface;

class AdminHelper
{
    public function __construct(
        private readonly CoreApiInterface $coreApi,
        private readonly TokenStore $tokenStore,
        private LoggerInterface $logger,
    ) {
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function getDefaultBaseUrl(): ?string
    {
        try {
            return $this->coreApi->getBaseUrl();
        } catch (BaseUrlNotDefinedExceptionInterface) {
            return null;
        }
    }

    public function login(string $baseUrl, string $username, string $password): CoreApiInterface
    {
        $this->coreApi
            ->setLogger($this->logger)
            ->authenticate($username, $password, $baseUrl);

        $this->tokenStore->saveToken($this->coreApi->getBaseUrl(), $this->coreApi->getToken());

        return $this->coreApi;
    }

    public function alreadyConnected(string $baseUrl, string $username): bool
    {
        if (null === $token = $this->tokenStore->getToken($baseUrl)) {
            return false;
        }

        $this->coreApi
            ->setLogger($this->logger)
            ->setBaseUrl($baseUrl)
            ->setToken($token);

        return $this->coreApi->user()->getProfileAuthenticated()->getUsername() === $username;
    }

    public function getCoreApi(): CoreApiInterface
    {
        return $this->coreApi
            ->setLogger($this->logger)
            ->setBaseUrl($this->tokenStore->getBaseUrl())
            ->setToken($this->tokenStore->giveToken());
    }
}
