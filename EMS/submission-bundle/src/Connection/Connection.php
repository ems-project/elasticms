<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Connection;

final class Connection
{
    /** @var array<string, string> */
    private array $connection;

    /**
     * @param array<string, string> $connection
     */
    public function __construct(array $connection)
    {
        $this->connection = $connection;
    }

    public function getKey(string $key): string
    {
        if (!isset($this->connection[$key])) {
            return $key;
        }

        return $this->connection[$key];
    }
}
