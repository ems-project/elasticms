<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiFactoryInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Storage\StorageManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\CurlHttpClient;

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
        $httpClient = new CurlHttpClient([
            'base_uri' => $baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'verify_host' => $this->verify,
            'verify_peer' => $this->verify,
            'timeout' => $this->timeout,
        ]);

        $coreApiClient = new Client($httpClient, $baseUrl, $this->logger);

        return new CoreApi($coreApiClient, $this->storageManager);
    }
}
