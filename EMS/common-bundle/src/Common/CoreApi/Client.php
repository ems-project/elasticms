<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi;

use EMS\CommonBundle\Common\CoreApi\Exception\BaseUrlNotDefinedException;
use EMS\CommonBundle\Common\CoreApi\Exception\NotAuthenticatedException;
use EMS\CommonBundle\Common\CoreApi\Exception\NotSuccessfulException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client
{
    /** @var array<string, string> */
    private array $headers = [];
    private string $baseUrl;
    private HttpClientInterface $client;
    private LoggerInterface $logger;

    public function __construct(string $baseUrl, LoggerInterface $logger, bool $insecure)
    {
        $this->baseUrl = $baseUrl;
        $this->client = new CurlHttpClient([
            'base_uri' => $baseUrl,
            'headers' => ['Content-Type' => 'application/json'],
            'verify_host' => !$insecure,
            'verify_peer' => !$insecure,
            'timeout' => 30,
        ]);

        $this->setLogger($logger);
    }

    public function addHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    public function getBaseUrl(): string
    {
        if ('' === $this->baseUrl) {
            throw new BaseUrlNotDefinedException();
        }

        return $this->baseUrl;
    }

    public function getHeader(string $name): string
    {
        return $this->headers[$name];
    }

    /**
     * @param array<mixed> $query
     */
    public function get(string $resource, array $query = []): Result
    {
        return $this->request(Request::METHOD_GET, $resource, [
            'headers' => $this->headers,
            'query' => $query,
        ]);
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, mixed> $options
     */
    public function post(string $resource, array $body = [], array $options = []): Result
    {
        return $this->request(Request::METHOD_POST, $resource, \array_merge($options, [
            'headers' => $this->headers,
            'json' => $body,
        ]));
    }

    public function delete(string $resource): Result
    {
        return $this->request(Request::METHOD_DELETE, $resource, [
            'headers' => $this->headers,
        ]);
    }

    public function head(string $resource): bool
    {
        $response = $this->client->request(Request::METHOD_HEAD, $resource, [
            'headers' => $this->headers,
        ]);

        return 200 === $response->getStatusCode();
    }

    public function postBody(string $resource, string $body): Result
    {
        return $this->request(Request::METHOD_POST, $resource, [
            'headers' => $this->headers,
            'body' => $body,
        ]);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;

        if ($this->client instanceof LoggerAwareInterface) {
            $this->client->setLogger($logger);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    private function request(string $method, string $resource, array $options): Result
    {
        if ('' === $this->baseUrl) {
            throw new BaseUrlNotDefinedException();
        }

        $response = $this->client->request($method, $resource, $options);

        if (Response::HTTP_UNAUTHORIZED === $response->getStatusCode()) {
            throw new NotAuthenticatedException($response);
        }

        $result = new Result($response, $this->logger);

        if (!$result->isSuccess()) {
            throw new NotSuccessfulException($response);
        }

        return $result;
    }
}
