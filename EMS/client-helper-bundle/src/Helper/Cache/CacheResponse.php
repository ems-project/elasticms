<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Cache;

use Psr\Cache\CacheItemInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class CacheResponse
{
    public const string HEADER_X_EMSCH_CACHE = 'X-emsch-cache';

    /**
     * @param array<mixed> $headers
     */
    public function __construct(private int $statusCode, private string $log, private array $headers = [], private ?string $content = null)
    {
    }

    public static function fromException(\Throwable $e): self
    {
        return new self(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
    }

    public static function isRunning(): self
    {
        return new self(Response::HTTP_ACCEPTED, 'SubRequest running');
    }

    public static function fromSubRequest(Response $response): self
    {
        if ($response instanceof StreamedResponse) {
            throw new \RuntimeException('Stream responses are not cacheable!');
        }

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \RuntimeException('No 200 response');
        }

        $content = $response->getContent();
        if (!\is_string($content)) {
            throw new \RuntimeException('The response without content');
        }

        return new self($response->getStatusCode(), 'Generated sub-request', $response->headers->all(), $content);
    }

    public static function fromCache(CacheItemInterface $cacheItem): self
    {
        /** @var array{status: int, log: string, headers: array<mixed>, content: ?string} $data */
        $data = $cacheItem->get();

        return new self($data['status'], $data['log'], $data['headers'], $data['content']);
    }

    public function getLog(): string
    {
        return \sprintf('Cached log message: %s', $this->log);
    }

    public function getResponse(): Response
    {
        $response = new Response($this->content, $this->statusCode, $this->headers);
        $response->headers->set(self::HEADER_X_EMSCH_CACHE, 'true');

        return $response;
    }

    /**
     * @return array{status: int, headers: array<mixed>, content: ?string, log: ?string}
     */
    public function getData(): array
    {
        return [
            'status' => $this->statusCode,
            'log' => $this->log,
            'headers' => $this->headers,
            'content' => $this->content,
        ];
    }
}
