<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiFactoryInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Storage\StorageManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\CurlHttpClient;

final readonly class CoreApiFactory implements CoreApiFactoryInterface
{
    /**
     * @param array{ headers: array<string, string>, max_connections: int, verify: bool, timeout: int } $options
     */
    public function __construct(
        private LoggerInterface $logger,
        private StorageManager $storageManager,
        private array $options,
    ) {
    }

    #[\Override]
    public function create(string $baseUrl): CoreApiInterface
    {
        $httpClient = new CurlHttpClient(
            defaultOptions: [
                'base_uri' => $baseUrl,
                'headers' => [
                    ...$this->options['headers'],
                    ...['Content-Type' => 'application/json'],
                ],
                'verify_host' => $this->options['verify'],
                'verify_peer' => $this->options['verify'],
                'timeout' => $this->options['timeout'],
            ],
            maxHostConnections: $this->options['max_connections']
        );

        $coreApiClient = new Client($httpClient, $baseUrl, $this->logger);

        return new CoreApi($coreApiClient, $this->storageManager);
    }
}
