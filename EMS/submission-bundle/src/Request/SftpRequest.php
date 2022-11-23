<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SftpRequest extends AbstractRequest
{
    /** @var array{host: string, port: int, username?: string, password?: string, privateKey?: string, root: string, timeout: int} */
    private $endpoint;
    /** @var array<mixed> */
    private $files;

    /**
     * @param array<string, mixed> $endpoint
     * @param array<mixed>         $files
     */
    public function __construct(array $endpoint, array $files)
    {
        /** @var array{host: string, port: int, username?: string, password?: string, privateKey?: string, root: string, timeout: int} $endpoint */
        $endpoint = $this->resolveEndpoint($endpoint);

        $this->endpoint = $endpoint;
        $this->files = $files;
    }

    public function getAdapter(): SftpAdapter
    {
        return new SftpAdapter(
            new SftpConnectionProvider(
                $this->endpoint['host'],
                $this->endpoint['username'] ?? '',
                $this->endpoint['password'] ?? '',
                $this->endpoint['privateKey'] ?? '',
                null,
                (int) $this->endpoint['port'],
                false,
                $this->endpoint['timeout']
            ),
            $this->endpoint['root']
        );
    }

    /**
     * @return array{host: string, port: int, username?: string, password?: string, privateKey?: string, root: string, timeout: int}
     */
    public function getEndpoint(): array
    {
        return $this->endpoint;
    }

    /**
     * @return \Generator<array{path: string, contents: string}>
     */
    public function getFiles(): \Generator
    {
        return $this->parseFiles($this->files);
    }

    protected function getEndpointOptionResolver(): OptionsResolver
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setRequired(['host', 'port',  'timeout'])
            ->setDefined(['username', 'password', 'privateKey'])
            ->setNormalizer('privateKey', function (Options $options, $value) {
                if ('' !== $value) {
                    $decode = \base64_decode($value);

                    return $decode ? $decode : 'invalid base64 encoding';
                }

                return $value;
            })
            ->setDefaults(['root' => '/', 'port' => 22, 'timeout' => 10]);

        return $optionsResolver;
    }
}
