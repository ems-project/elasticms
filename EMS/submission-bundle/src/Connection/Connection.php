<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Connection;

final class Connection
{
    /**
     * @param array<string, string> $connection
     */
    public function __construct(private array $connection)
    {
    }

    public function getKey(string $key): string
    {
        if (!isset($this->connection[$key])) {
            return $key;
        }

        return $this->connection[$key];
    }
}
