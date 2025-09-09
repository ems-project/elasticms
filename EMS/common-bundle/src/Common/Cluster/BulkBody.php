<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Cluster;

use EMS\Helpers\Standard\Json;

class BulkBody
{
    private string $body = '';

    /**
     * @param mixed[] $source
     */
    public function index(string $id, array $source): void
    {
        $this->body .= Json::encode(['index' => [SimpleIndexClient::ID => $id]]).PHP_EOL;
        $this->body .= Json::encode($source).PHP_EOL;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function empty(): bool
    {
        return '' === $this->body;
    }
}
