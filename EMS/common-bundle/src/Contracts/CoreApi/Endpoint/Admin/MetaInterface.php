<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin;

interface MetaInterface
{
    public function getDefaultContentTypeEnvironmentAlias(string $contentTypeName): string;
}
