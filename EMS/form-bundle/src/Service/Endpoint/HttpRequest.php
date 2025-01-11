<?php

declare(strict_types=1);

namespace EMS\FormBundle\Service\Endpoint;

use Symfony\Component\HttpFoundation\Request;

final readonly class HttpRequest
{
    private string $method;
    private string $url;
    /** @var mixed[] */
    private array $headers;
    private string $body;
    /** @var array<string, mixed> */
    private array $options;

    /** @param array<string, mixed> $config */
    public function __construct(array $config)
    {
        $this->method = $config['method'] ?? Request::METHOD_POST;
        $this->url = $config['url'];
        $this->headers = $config['headers'] ?? [];
        $this->body = $config['body'] ?? '';
        $this->options = $config['options'] ?? [];
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /** @return mixed[] */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /** @return mixed[] */
    public function getOptions(): array
    {
        return $this->options;
    }

    /** @param array<string, string> $replace */
    public function createBody(array $replace): string
    {
        return \str_replace(\array_keys($replace), \array_values($replace), $this->body);
    }
}
