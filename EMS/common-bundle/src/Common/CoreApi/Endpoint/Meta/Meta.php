<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Meta;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\MetaInterface;

final readonly class Meta implements MetaInterface
{
    public function __construct(private Client $client)
    {
    }

    #[\Override]
    public function getDefaultContentTypeEnvironmentAlias(string $contentTypeName): string
    {
        /** @var array{alias: string} $meta */
        $meta = $this->client->get(\implode('/', ['api', 'meta', 'content-type', $contentTypeName]))->getData();

        return $meta['alias'];
    }

    #[\Override]
    public function getInfoDocuments(array $environments, array $emsLinks): array
    {
        $data = $this->client->post(\implode('/', ['api', 'meta', 'info', 'documents']), [
            'environments' => $environments,
            'emsLinks' => $emsLinks,
        ])->getData();

        return $data['info'] ?? [];
    }

    #[\Override]
    public function getDrafts(bool $includeRawData = false, array $circles = []): array
    {
        return $this->client->get('/api/meta/drafts', [
            'includeRawData' => $includeRawData,
            'circles' => $circles,
        ])->getData();
    }

    #[\Override]
    public function getEnvironments(?bool $managed = null, ?bool $snapshot = null): array
    {
        $query = \array_filter([
            'managed' => $managed,
            'snapshot' => $snapshot,
        ], static fn ($value) => null !== $value);

        return $this->client->get('/api/meta/environments', $query)->getData();
    }

    public function aliasAttachEnvironment(string $alias, string $environment): bool
    {
        return $this->client->post(\implode('/', ['api', 'meta', 'alias-attach-environment']), [
            'alias' => $alias,
            'environment' => $environment,
        ])->isSuccess();
    }
}
