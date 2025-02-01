<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use Psr\Log\LoggerInterface;

class SftpStorage extends AbstractUrlStorage
{
    /** @var resource|null */
    private $sftp;

    public function __construct(LoggerInterface $logger, private readonly string $host, private readonly string $path, private readonly string $username, private readonly string $publicKeyFile, private readonly string $privateKeyFile, int $usage, int $hotSynchronizeLimit = 0, private readonly ?string $passwordPhrase = null, private readonly int $port = 22)
    {
        parent::__construct($logger, $usage, $hotSynchronizeLimit);
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
            throw new \RuntimeException("PHP functions Secure Shell are required by $this. (ssh2)");
        }

        $connection = @\ssh2_connect($this->host, $this->port);
        if (false === $connection) {
            throw new \Exception("Could not connect to $this->host on port $this->port.");
        }

        if (null === $this->passwordPhrase) {
            \ssh2_auth_pubkey_file($connection, $this->username, $this->publicKeyFile, $this->privateKeyFile);
        } else {
            \ssh2_auth_pubkey_file($connection, $this->username, $this->publicKeyFile, $this->privateKeyFile, $this->passwordPhrase);
        }

        $sftp = @\ssh2_sftp($connection);
        if (false === $sftp) {
            throw new \Exception("Could not initialize SFTP subsystem to $this->host");
        }

        $this->sftp = $sftp;
    }

    #[\Override]
    public function __toString(): string
    {
        return SftpStorage::class." ($this->host)";
    }

    /**
     * @return null
     */
    #[\Override]
    protected function getContext()
    {
        return null;
    }
}
