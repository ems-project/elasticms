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

    /**
     * @param string[] $circles
     *
     * @return array<int, array{
     *     id: string,
     *     ouuid: ?string,
     *     circles: string[],
     *     save_date: string,
     *     created: string,
     *     raw_data?: array<mixed>
     * }>
     */
    public function getDrafts(bool $includeRawData = false, array $circles = []): array;

    /**
     * @return array<int, array{ name: string, managed: bool, snapshot: bool}>
     */
    public function getEnvironments(?bool $managed = null, ?bool $snapshot = null): array;

    public function aliasAttachEnvironment(string $alias, string $environment): bool;
}
