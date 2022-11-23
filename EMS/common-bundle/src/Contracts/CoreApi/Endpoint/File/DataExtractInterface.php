<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\File;

interface DataExtractInterface
{
    /**
     * @return array<string, mixed>
     */
    public function get(string $hash): array;
}
