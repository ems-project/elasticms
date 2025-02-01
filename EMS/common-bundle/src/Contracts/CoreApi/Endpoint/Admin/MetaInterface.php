<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin;

interface MetaInterface
{
    public function getDefaultContentTypeEnvironmentAlias(string $contentTypeName): string;

    /**
     * @param list<string> $environments
     * @param list<string> $emsLinks
     *
     * @return array<string, array{
     *      'id': int,
     *      'draft': bool,
     *      'revisions': array<string, ?int>,
     *      'status': array<string, 'not_published'|'outdated'|'published'>
     *  }>
     */
    public function getInfoDocuments(array $environments, array $emsLinks): array;
}
