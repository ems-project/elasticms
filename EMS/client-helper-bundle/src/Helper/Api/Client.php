<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Api;

use EMS\CommonBundle\Common\HttpClientFactory;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\Helpers\Standard\Json;
use GuzzleHttp\Client as HttpClient;

/**
 * @todo use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface
 */
class Client
{
    private ?HttpClient $client = null;

    public function __construct(
        private readonly string $name,
        private readonly ?string $baseUrl,
        private readonly ?string $key,
        private readonly CoreApiInterface $coreApi,
    ) {
    }

    public function getCoreApi(): CoreApiInterface
    {
        return $this->coreApi->setBaseUrl($this->baseUrl);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param array<mixed> $body
     *
     * @return array<mixed>
     */
    public function initNewDocument(string $type, array $body, ?string $ouuid = null): array
    {
        $url = null === $ouuid ? \sprintf('api/data/%s/draft', $type) : \sprintf('api/data/%s/draft/%s', $type, $ouuid);

        $response = $this->getClient()->post(
            $url,
            ['body' => Json::encode($body)]
        );

        return Json::decode($response->getBody()->getContents());
    }

    /**
     * @param array<mixed> $body
     *
     * @return array<mixed>
     */
    public function updateDocument(string $type, ?string $ouuid, array $body): array
    {
        $response = $this->getClient()->post(
            \sprintf('/api/data/%s/replace/%s', $type, $ouuid),
            ['body' => Json::encode($body)]
        );

        return Json::decode($response->getBody()->getContents());
    }

    /**
     * @return array<mixed>
     */
    public function finalize(string $type, int $revisionId): array
    {
        $response = $this->getClient()->post(
            \sprintf('api/data/%s/finalize/%d', $type, $revisionId)
        );

        return Json::decode($response->getBody()->getContents());
    }

    /**
     * @return array<mixed>
     */
    public function discardDraft(string $type, int $revisionId)
    {
        $response = $this->getClient()->post(
            \sprintf('api/data/%s/discard/%d', $type, $revisionId)
        );

        return Json::decode($response->getBody()->getContents());
    }

    /**
     * @return array<mixed>
     */
    public function postFile(\SplFileInfo $file, ?string $forcedFilename = null): array
    {
        $response = $this->getClient()->post('api/file/upload', [
            'multipart' => [
                [
                    'name' => 'upload',
                    'contents' => \fopen($file->getPathname(), 'r'),
                    'filename' => $forcedFilename ?? $file->getFilename(),
                ],
            ],
        ]);

        return Json::decode($response->getBody()->getContents());
    }

    private function getClient(): HttpClient
    {
        if (null !== $this->client) {
            return $this->client;
        }

        if (null === $this->baseUrl) {
            throw new \RuntimeException('Missing "EMSCH_BACKEND_URL" not defined');
        }
        if (null === $this->key) {
            throw new \RuntimeException('Missing "EMSCH_BACKEND_API_KEY" not defined');
        }

        $this->client = HttpClientFactory::create($this->baseUrl, ['X-Auth-Token' => $this->key]);

        return $this->client;
    }
}
