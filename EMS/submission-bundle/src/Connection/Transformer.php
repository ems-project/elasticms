<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Connection;

final class Transformer
{
    /**
     * @var array<array<string, string>>
     */
    private array $connections;

    /**
     * @param array<array<string, string>> $connections
     */
    public function __construct(array $connections)
    {
        $this->connections = $connections;
    }

    /**
     * @param array<string> $path ['connectionName', 'password']
     */
    public function transform(array $path): string
    {
        if (empty($path)) {
            return '';
        }

        if (1 === \count($path)) {
            return $path[0];
        }

        $conn = $this->getConnection($path[0]);

        return null === $conn ? $path[0] : $conn->getKey($path[1]);
    }

    private function getConnection(string $name): ?Connection
    {
        foreach ($this->connections as $connection) {
            if (!isset($connection['connection']) || $connection['connection'] != $name) {
                continue;
            }

            return new Connection($connection);
        }

        return null;
    }
}
