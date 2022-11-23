<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use Psr\Log\LoggerInterface;

class SftpStorage extends AbstractUrlStorage
{
    private string $host;
    private string $path;
    private int $port;
    private string $username;
    private string $publicKeyFile;
    private string $privateKeyFile;
    private ?string $passwordPhrase;
    /** @var resource|null */
    private $sftp = null;

    /**
     * @param null $passwordPhrase
     */
    public function __construct(LoggerInterface $logger, string $host, string $path, string $username, string $publicKeyFile, string $privateKeyFile, int $usage, int $hotSynchronizeLimit = 0, ?string $passwordPhrase = null, int $port = 22)
    {
        parent::__construct($logger, $usage, $hotSynchronizeLimit);
        $this->host = $host;
        $this->path = $path;
        $this->port = $port;

        $this->username = $username;
        $this->publicKeyFile = $publicKeyFile;
        $this->privateKeyFile = $privateKeyFile;
        $this->passwordPhrase = $passwordPhrase;
    }

    protected function getBaseUrl(): string
    {
        if (null === $this->sftp) {
            $this->connect();
        }

        return 'ssh2.sftp://'.\intval($this->sftp).$this->path;
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

    public function __toString(): string
    {
        return SftpStorage::class." ($this->host)";
    }

    /**
     * @return null
     */
    protected function getContext()
    {
        return null;
    }
}
