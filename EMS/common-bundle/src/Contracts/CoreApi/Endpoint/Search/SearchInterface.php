<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\Search;

use EMS\CommonBundle\Common\CoreApi\Search\Scroll;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\CommonBundle\Elasticsearch\Response\ResponseInterface;
use EMS\CommonBundle\Search\Search as SearchObject;

interface SearchInterface
{
    public function search(SearchObject $search): ResponseInterface;

    public function count(SearchObject $search): int;

    public function scroll(SearchObject $search, int $scrollSize = 10, string $expireTime = '3m'): Scroll;

    public function version(): string;

    public function healthStatus(): string;

    public function refresh(?string $index = null): bool;

    /**
     * @return string[]
     */
    public function getIndicesFromAlias(string $alias): array;

    /**
     * @return string[]
     */
    public function getAliasesFromIndex(string $index): array;

    /**
     * @param string[] $sourceIncludes
     * @param string[] $sourcesExcludes
     */
    public function getDocument(string $index, ?string $contentType, string $id, array $sourceIncludes = [], array $sourcesExcludes = []): DocumentInterface;
}
