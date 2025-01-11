<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Api;

use EMS\CommonBundle\Common\CoreApi\CoreApi;
use EMS\CommonBundle\Common\HttpClientFactory;
use EMS\Helpers\Standard\Json;
use GuzzleHttp\Client as HttpClient;

/**
 * @todo use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface
 */
final readonly class Client
{
    private HttpClient $client;

    public function __construct(
        private string $name,
        string $baseUrl,
        private string $key,
        public CoreApi $coreApi,
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
        $response = $this->client->post(
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
        $response = $this->client->post(
            \sprintf('api/data/%s/finalize/%d', $type, $revisionId)
        );

        return Json::decode($response->getBody()->getContents());
    }

    /**
     * @return array<mixed>
     */
    public function discardDraft(string $type, int $revisionId)
    {
        $response = $this->client->post(
            \sprintf('api/data/%s/discard/%d', $type, $revisionId)
        );

        return Json::decode($response->getBody()->getContents());
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

        return Json::decode($response->getBody()->getContents());
    }
}
