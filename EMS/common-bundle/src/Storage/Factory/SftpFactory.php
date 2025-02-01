<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Factory;

use EMS\CommonBundle\Storage\Service\SftpStorage;
use EMS\CommonBundle\Storage\Service\StorageInterface;
use Psr\Log\LoggerInterface;

class SftpFactory extends AbstractFactory implements StorageFactoryInterface
{
    final public const string STORAGE_TYPE = 'sftp';
    final public const string STORAGE_CONFIG_HOST = 'host';
    final public const string STORAGE_CONFIG_PATH = 'path';
    final public const string STORAGE_CONFIG_USERNAME = 'username';
    final public const string STORAGE_CONFIG_PUBLIC_KEY_FILE = 'public-key-file';
    final public const string STORAGE_CONFIG_PRIVATE_KEY_FILE = 'private-key-file';
    final public const string STORAGE_CONFIG_PASSWORD_PHRASE = 'password-phrase';
    final public const string STORAGE_CONFIG_PORT = 'port';

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    #[\Override]
    public function createService(array $parameters): ?StorageInterface
    {
        $config = $this->resolveParameters($parameters);

        $host = $config[self::STORAGE_CONFIG_HOST];
        if (null === $host || '' === $host) {
            @\trigger_error('You should consider to migrate you storage service configuration to the EMS_STORAGES variable', \E_USER_DEPRECATED);

            return null;
        }
        $path = $config[self::STORAGE_CONFIG_PATH];
        $username = $config[self::STORAGE_CONFIG_USERNAME];
        $publicKeyFile = $config[self::STORAGE_CONFIG_PUBLIC_KEY_FILE];
        $privateKeyFile = $config[self::STORAGE_CONFIG_PRIVATE_KEY_FILE];
        $passwordPhrase = $config[self::STORAGE_CONFIG_PASSWORD_PHRASE];
        $port = (int) $config[self::STORAGE_CONFIG_PORT];

        return new SftpStorage($this->logger, $host, $path, $username, $publicKeyFile, $privateKeyFile, $config[self::STORAGE_CONFIG_USAGE], $config[self::STORAGE_CONFIG_HOT_SYNCHRONIZE_LIMIT], $passwordPhrase, $port);
    }

    #[\Override]
    public function getStorageType(): string
    {
        return self::STORAGE_TYPE;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array{type: string, host: string|null, path: string, username: string, public-key-file: string, public-key-file: string, private-key-file: string, password-phrase: string|null, port: int, usage: int, hot-synchronize-limit: int}
     */
    private function resolveParameters(array $parameters): array
    {
        $resolver = $this->getDefaultOptionsResolver();
        $resolver
            ->setDefaults([
                self::STORAGE_CONFIG_TYPE => self::STORAGE_TYPE,
                self::STORAGE_CONFIG_HOST => null,
                self::STORAGE_CONFIG_PATH => null,
                self::STORAGE_CONFIG_USERNAME => null,
                self::STORAGE_CONFIG_PUBLIC_KEY_FILE => null,
                self::STORAGE_CONFIG_PRIVATE_KEY_FILE => null,
                self::STORAGE_CONFIG_PASSWORD_PHRASE => null,
                self::STORAGE_CONFIG_PORT => 22,
                self::STORAGE_CONFIG_USAGE => StorageInterface::STORAGE_USAGE_BACKUP_ATTRIBUTE,
            ])
            ->setRequired([
                self::STORAGE_CONFIG_TYPE,
                self::STORAGE_CONFIG_PATH,
                self::STORAGE_CONFIG_USERNAME,
                self::STORAGE_CONFIG_PUBLIC_KEY_FILE,
                self::STORAGE_CONFIG_PRIVATE_KEY_FILE,
                self::STORAGE_CONFIG_PORT,
            ])
            ->setAllowedTypes(self::STORAGE_CONFIG_HOST, ['string', 'null'])
            ->setAllowedTypes(self::STORAGE_CONFIG_PATH, 'string')
            ->setAllowedTypes(self::STORAGE_CONFIG_USERNAME, 'string')
            ->setAllowedTypes(self::STORAGE_CONFIG_PUBLIC_KEY_FILE, 'string')
            ->setAllowedTypes(self::STORAGE_CONFIG_PRIVATE_KEY_FILE, 'string')
            ->setAllowedTypes(self::STORAGE_CONFIG_PORT, 'int')
            ->setAllowedValues(self::STORAGE_CONFIG_TYPE, [self::STORAGE_TYPE])
        ;

        /** @var array{type: string, host: string|null, path: string, username: string, public-key-file: string, public-key-file: string, private-key-file: string, password-phrase: string|null, port: int, usage: int, hot-synchronize-limit: int} $resolvedParameter */
        $resolvedParameter = $resolver->resolve($parameters);

        return $resolvedParameter;
    }
}
