<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use Psr\Log\LoggerInterface;

class SftpStorage extends AbstractUrlStorage implements \Stringable
{
    /** @var resource|null */
    private $sftp;

    public function __construct(
        LoggerInterface $logger,
        private readonly string $host,
        private readonly string $path,
        private readonly string $username,
        private readonly string $publicKeyFile,
        private readonly string $privateKeyFile,
        int $usage,
        int $hotSynchronizeLimit = 0,
        private readonly ?string $passwordPhrase = null,
        private readonly int $port = 22,
        int $retryDelay = 0
    ) {
        parent::__construct($logger, $usage, $hotSynchronizeLimit, $retryDelay);
    }

    #[\Override]
    protected function getBaseUrl(): string
    {
        if (null === $this->sftp) {
            $this->connect();
        }

        return 'ssh2.sftp://'.(int) $this->sftp.$this->path;
    }

    private function connect(): void
    {
        if (!\function_exists('ssh2_connect')) {
            throw new \RuntimeException(\sprintf('PHP functions Secure Shell are required by %s. (ssh2)', $this));
        }

        $connection = @ssh2_connect($this->host, $this->port);
        if (false === $connection) {
            throw new \Exception(\sprintf('Could not connect to %s on port %d.', $this->host, $this->port));
        }

        if (null === $this->passwordPhrase) {
            ssh2_auth_pubkey_file($connection, $this->username, $this->publicKeyFile, $this->privateKeyFile);
        } else {
            ssh2_auth_pubkey_file($connection, $this->username, $this->publicKeyFile, $this->privateKeyFile, $this->passwordPhrase);
        }

        $sftp = @ssh2_sftp($connection);
        if (false === $sftp) {
            throw new \Exception('Could not initialize SFTP subsystem to '.$this->host);
        }

        $this->sftp = $sftp;
    }

    #[\Override]
    public function __toString(): string
    {
        return SftpStorage::class.\sprintf(' (%s)', $this->host);
    }

    #[\Override]
    protected function getContext(): null
    {
        return null;
    }
}
