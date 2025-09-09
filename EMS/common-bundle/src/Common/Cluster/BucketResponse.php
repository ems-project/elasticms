<?php

namespace EMS\CommonBundle\Common\Cluster;

use EMS\Helpers\Standard\Type;

class BucketResponse
{
    /**
     * @param mixed[] $response
     */
    public function __construct(private readonly array $response)
    {
    }

    public function getKey(): string
    {
        return Type::string($this->response['key']);
    }
}