<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiFactoryInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Storage\StorageManager;
use Psr\Log\LoggerInterface;

final class CoreApiFactory implements CoreApiFactoryInterface
{
    private LoggerInterface $logger;
    private StorageManager $storageManager;
    private bool $insecure;

    public function __construct(LoggerInterface $logger, StorageManager $storageManager, bool $insecure = false)
    {
        $this->logger = $logger;
        $this->storageManager = $storageManager;
        $this->insecure = $insecure;
    }

    public function create(string $baseUrl): CoreApiInterface
    {
        $client = new Client($baseUrl, $this->logger, $this->insecure);

        return new CoreApi($client, $this->storageManager);
    }
}
