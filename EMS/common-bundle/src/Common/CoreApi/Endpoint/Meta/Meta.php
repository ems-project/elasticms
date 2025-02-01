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
}
