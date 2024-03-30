<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Api;

use EMS\CommonBundle\Common\CoreApi\CoreApi;
use EMS\CommonBundle\Common\HttpClientFactory;
use GuzzleHttp\Client as HttpClient;

/**
 * @todo use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface
 */
final class Client
{
    private readonly HttpClient $client;

    public function __construct(
        private readonly string $name,
        string $baseUrl,
        private readonly string $key,
        public readonly CoreApi $coreApi,
    ) {
        $this->client = HttpClientFactory::create($baseUrl, ['X-Auth-Token' => $this->key]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @deprecated
     *
     * @param array<mixed> $body
     *
     * @return array<mixed>
     */
    public function createDraft(string $type, array $body, ?string $ouuid = null): array
    {
        @\trigger_error('Deprecated use the initNewDocument or initNewDraftRevision functions', E_USER_DEPRECATED);

        return $this->initNewDocument($type, $body, $ouuid);
    }

    /**
     * @param array<mixed> $body
     *
     * @return array<mixed>
     */
    public function initNewDocument(string $type, array $body, ?string $ouuid = null): array
    {
        if (null === $ouuid) {
            $url = \sprintf('api/data/%s/draft', $type);
        } else {
            $url = \sprintf('api/data/%s/draft/%s', $type, $ouuid);
        }

        $response = $this->client->post(
            $url,
            ['body' => \json_encode($body, JSON_THROW_ON_ERROR)]
        );

        return \json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<mixed> $body
     *
     * @return array<mixed>
     */
    public function updateDocument(string $type, ?string $ouuid, array $body): array
    {
        $response = $this->client->post(
            \sprintf('/api/data/%s/replace/%s', $type, $ouuid),
            ['body' => \json_encode($body, JSON_THROW_ON_ERROR)]
        );

        return \json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<mixed>
     */
    public function finalize(string $type, int $revisionId): array
    {
        $response = $this->client->post(
            \sprintf('api/data/%s/finalize/%d', $type, $revisionId)
        );

        return \json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<mixed>
     */
    public function discardDraft(string $type, int $revisionId)
    {
        $response = $this->client->post(
            \sprintf('api/data/%s/discard/%d', $type, $revisionId)
        );

        return \json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<mixed>
     */
    public function postFile(\SplFileInfo $file, ?string $forcedFilename = null): array
    {
        $response = $this->client->post('api/file/upload', [
            'multipart' => [
                [
                    'name' => 'upload',
                    'contents' => \fopen($file->getPathname(), 'r'),
                    'filename' => $forcedFilename ?? $file->getFilename(),
                ],
            ],
        ]);

        return \json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }
}
