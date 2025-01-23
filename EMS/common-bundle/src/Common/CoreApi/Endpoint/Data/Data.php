<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Data;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DraftInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\RevisionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class Data implements DataInterface
{
    /** @var string[] */
    private array $endPoint;

    public function __construct(private Client $client, string $contentType)
    {
        $this->endPoint = ['api', 'data', $contentType];
    }

    #[\Override]
    public function autoSave(int $revisionId, array $rawData): bool
    {
        $resource = $this->makeResource('auto-save', (string) $revisionId);

        return $this->client->post($resource, $rawData)->isSuccess();
    }

    /**
     * @param array<string, mixed> $rawData
     */
    #[\Override]
    public function create(array $rawData, ?string $ouuid = null): DraftInterface
    {
        $resource = $this->makeResource('create', $ouuid);

        return new Draft($this->client->post($resource, $rawData));
    }

    #[\Override]
    public function discard(int $revisionId): bool
    {
        $resource = $this->makeResource('discard', \strval($revisionId));

        return $this->client->post($resource)->isSuccess();
    }

    #[\Override]
    public function delete(string $ouuid): bool
    {
        $resource = $this->makeResource('delete', $ouuid);

        return $this->client->post($resource)->isSuccess();
    }

    #[\Override]
    public function finalize(int $revisionId, array $rawData = []): string
    {
        $resource = $this->makeResource('finalize', (string) $revisionId);

        $data = $this->client->post($resource, $rawData)->getData();

        return $data['ouuid'];
    }

    #[\Override]
    public function get(string $ouuid): RevisionInterface
    {
        $resource = $this->makeResource($ouuid);

        return new Revision($this->client->get($resource));
    }

    #[\Override]
    public function getDraft(int $revisionId): array
    {
        $resource = $this->makeResource('draft', (string) $revisionId);
        $response = $this->client->get($resource)->getData();

        return [
            'id' => $response['id'],
            'data' => $response['data'],
        ];
    }

    /**
     * @param array<string, mixed> $rawData
     */
    #[\Override]
    public function replace(string $ouuid, array $rawData): DraftInterface
    {
        $resource = $this->makeResource('replace', $ouuid);

        return new Draft($this->client->post($resource, $rawData));
    }

    /**
     * @param array<string, mixed> $rawData
     */
    #[\Override]
    public function update(string $ouuid, array $rawData): DraftInterface
    {
        $resource = $this->makeResource('merge', $ouuid);

        return new Draft($this->client->post($resource, $rawData));
    }

    #[\Override]
    public function head(string $ouuid): bool
    {
        $resource = $this->makeResource($ouuid);

        return $this->client->head($resource);
    }

    /**
     * @param array<string, mixed> $rawData
     */
    #[\Override]
    public function save(string $ouuid, array $rawData, int $mode = self::MODE_UPDATE, bool $discardDraft = true): int
    {
        return $this->index($ouuid, $rawData, self::MODE_UPDATE === $mode)->getRevisionId();
    }

    /**
     * @param array<string, mixed> $rawData
     */
    #[\Override]
    public function index(?string $ouuid, array $rawData, bool $merge = false, bool $refresh = false): Index
    {
        $resource = $this->makeResource($merge && $ouuid ? 'update' : 'index', $ouuid);

        if ($refresh) {
            $resource .= '?refresh=true';
        }

        return new Index($this->client->post($resource, $rawData));
    }

    #[\Override]
    public function indexAsync(?string $ouuid, array $rawData, bool $merge = false, bool $refresh = false): ResponseInterface
    {
        $resource = $this->makeResource($merge && $ouuid ? 'update' : 'index', $ouuid);

        if ($refresh) {
            $resource .= '?refresh=true';
        }

        return $this->client->asyncRequest(Request::METHOD_POST, $resource, ['json' => $rawData]);
    }

    #[\Override]
    public function publish(string $ouuid, string $environment, ?string $revisionId = null): bool
    {
        $resource = $this->makeResource('publish', $ouuid, $environment, $revisionId ?? '');
        $success = $this->client->post($resource)->getData()['success'] ?? null;
        if (!\is_bool($success)) {
            throw new \RuntimeException('Unexpected: search must be a boolean');
        }

        return $success;
    }

    private function makeResource(?string ...$path): string
    {
        return \implode('/', \array_merge($this->endPoint, \array_filter($path)));
    }
}
