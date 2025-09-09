<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Cluster;

use EMS\Helpers\Standard\Type;

class HitResult
{
    /**
     * @param mixed[] $response
     */
    public function __construct(private readonly array $response)
    {
    }

    public function getId(): string
    {
        return Type::string($this->response['_id']);
    }

    /**
     * @return mixed[]
     */
    public function getSource(): array
    {
        return Type::array($this->response['_source']);
    }

    public function get(string $field, mixed $default = null): mixed
    {
        return $this->response['_source'][$field] ?? $default;
    }
}
