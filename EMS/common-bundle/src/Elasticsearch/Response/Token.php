<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Response;

use EMS\Helpers\Standard\Type;

class Token
{
    /**
     * @param array<string, int|string> $token
     */
    public function __construct(private readonly array $token)
    {
    }

    public function getToken(): string
    {
        return Type::string($this->token['token']);
    }

    public function __toString(): string
    {
        return $this->getToken();
    }
}
