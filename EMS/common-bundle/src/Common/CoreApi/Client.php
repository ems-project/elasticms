<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi;

use EMS\CommonBundle\Common\CoreApi\Exception\BaseUrlNotDefinedException;
use EMS\CommonBundle\Common\CoreApi\Exception\NotAuthenticatedException;
use EMS\CommonBundle\Common\CoreApi\Exception\NotSuccessfulException;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\Response\StreamableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Client
{
    /** @var array<string, string> */
    private array $headers = [];
    private readonly HttpClientInterface $client;
    private LoggerInterface $logger;

    public function __construct(private readonly string $baseUrl, LoggerInterface $logger, bool $verify)
    {
        $this->client = new CurlHttpClient([
            'base_uri' => $baseUrl,
            'headers' => ['Content-Type' => 'application/json'],
            'verify_host' => $verify,
            'verify_peer' => $verify,
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
        return $this->getResult(Request::METHOD_GET, $resource, [
            'headers' => $this->headers,
            'query' => $query,
        ]);
    }

    /**
     * @param array<mixed> $query
     */
    public function download(string $resource, array $query = []): StreamInterface
    {
        $response = $this->getResponse(Request::METHOD_GET, $resource, [
            'headers' => $this->headers,
            'query' => $query,
        ]);

        if (!$response instanceof StreamableInterface) {
            throw new \RuntimeException('no stream response');
        }

        return new Stream($response->toStream());
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, mixed> $options
     */
    public function post(string $resource, array $body = [], array $options = []): Result
    {
        return $this->getResult(Request::METHOD_POST, $resource, \array_merge($options, [
            'headers' => $this->headers,
            'json' => $body,
        ]));
    }

    public function delete(string $resource): Result
    {
        return $this->getResult(Request::METHOD_DELETE, $resource, [
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
        return $this->getResult(Request::METHOD_POST, $resource, [
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
    private function getResponse(string $method, string $resource, array $options): ResponseInterface
    {
        if ('' === $this->baseUrl) {
            throw new BaseUrlNotDefinedException();
        }

        $response = $this->client->request($method, $resource, $options);

        if (Response::HTTP_UNAUTHORIZED === $response->getStatusCode()) {
            throw new NotAuthenticatedException($response);
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function getResult(string $method, string $resource, array $options): Result
    {
        $response = $this->getResponse($method, $resource, $options);
        $result = new Result($response, $this->logger);

        if (!$result->isSuccess()) {
            throw new NotSuccessfulException($response);
        }

        return $result;
    }
}
