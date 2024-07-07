<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiFactoryInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Storage\StorageManager;
use Psr\Log\LoggerInterface;

final class CoreApiFactory implements CoreApiFactoryInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly StorageManager $storageManager,
        private readonly bool $verify = true,
        private readonly int $timeout = 30,
    ) {
    }

    public function create(string $baseUrl): CoreApiInterface
    {
        $client = new Client($baseUrl, $this->logger, $this->verify, $this->timeout);

        return new CoreApi($client, $this->storageManager);
    }
}
