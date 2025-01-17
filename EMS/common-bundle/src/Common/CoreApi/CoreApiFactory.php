<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiFactoryInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Storage\StorageManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class CoreApiFactory implements CoreApiFactoryInterface
{
    /**
     * @param array{ headers: array<string, string>, verify: bool, timeout: int } $options
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private StorageManager $storageManager,
        private array $options,
        private ?string $defaultUrl = null,
        private ?string $defaultToken = null,
    ) {
    }

    #[\Override]
    public function create(?string $baseUrl = null): CoreApiInterface
    {
        $httpClient = $this->httpClient->withOptions(
            new HttpOptions()
                ->setHeaders([
                    ...$this->options['headers'],
                    ...['Content-Type' => 'application/json'],
                ])
                ->verifyHost($this->options['verify'])
                ->verifyPeer($this->options['verify'])
                ->setTimeout($this->options['timeout'])
                ->toArray()
        );

        $coreApi = new CoreApi(
            client: new Client($httpClient, $this->logger),
            storageManager: $this->storageManager
        );
        $coreApi->setBaseUrl($baseUrl ?? $this->defaultUrl);

        if (null !== $this->defaultToken && '' !== $this->defaultToken) {
            $coreApi->setToken($this->defaultToken);
        }

        return $coreApi;
    }
}
