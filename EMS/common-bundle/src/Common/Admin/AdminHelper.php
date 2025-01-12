<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Admin;

use EMS\CommonBundle\Common\CoreApi\TokenStore;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiFactoryInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Psr\Log\LoggerInterface;

class AdminHelper
{
    private ?CoreApiInterface $coreApi = null;

    public function __construct(
        private readonly CoreApiFactoryInterface $coreApiFactory,
        private readonly TokenStore $tokenStore,
        private LoggerInterface $logger,
    ) {
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function login(string $baseUrl, string $username, string $password): CoreApiInterface
    {
        $this->coreApi = $this->coreApiFactory->create($baseUrl);
        $this->coreApi->setLogger($this->logger);
        $this->coreApi->authenticate($username, $password);
        $this->tokenStore->saveToken($this->coreApi->getBaseUrl(), $this->coreApi->getToken());

        return $this->coreApi;
    }

    public function alreadyConnected(string $baseUrl, string $username): bool
    {
        if (null !== $this->coreApi) {
            return true;
        }
        $coreApi = $this->coreApiFactory->create($baseUrl);
        $coreApi->setLogger($this->logger);
        $token = $this->tokenStore->getToken($baseUrl);
        if (!\is_string($token)) {
            return false;
        }
        $coreApi->setToken($token);
        $user = $coreApi->user();
        if ($user->getProfileAuthenticated()->getUsername() === $username) {
            $this->coreApi = $coreApi;

            return true;
        }

        return false;
    }

    public function getCoreApi(): CoreApiInterface
    {
        if (null !== $this->coreApi) {
            return $this->coreApi;
        }
        $this->coreApi = $this->coreApiFactory->create($this->tokenStore->giveBaseUrl());
        $this->coreApi->setLogger($this->logger);
        $this->coreApi->setToken($this->tokenStore->giveToken());

        return $this->coreApi;
    }
}
