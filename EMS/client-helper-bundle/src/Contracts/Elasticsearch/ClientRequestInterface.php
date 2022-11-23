<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Contracts\Elasticsearch;

use EMS\ClientHelperBundle\Contracts\ContentType\ContentTypeInterface;
use EMS\ClientHelperBundle\Helper\Environment\Environment;

interface ClientRequestInterface
{
    public function getCacheKey(string $prefix = '', string $environment = null): string;

    public function getContentType(string $name, ?Environment $environment = null): ?ContentTypeInterface;

    /**
     * @return array<mixed>
     */
    public function get(string $type, string $id): array;

    /**
     * @param string[] $sourceFields
     *
     * @return array<string, mixed>|false
     */
    public function getByEmsKey(string $emsLink, array $sourceFields = []);

    /**
     * @param string[] $ouuids
     *
     * @return array<mixed>
     */
    public function getByOuuids(string $type, array $ouuids): array;

    /**
     * @param mixed $default
     *
     * @return mixed|null
     */
    public function getOption(string $propertyPath, $default = null);
}
