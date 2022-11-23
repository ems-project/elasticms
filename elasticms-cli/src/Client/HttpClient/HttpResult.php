<?php

declare(strict_types=1);

namespace App\Client\HttpClient;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class HttpResult
{
    private ?ResponseInterface $response;
    private ?string $errorMessage;

    public function __construct(?ResponseInterface $response, string $errorMessage = null)
    {
        $this->response = $response;
        $this->errorMessage = $errorMessage;
        if (null === $this->response && null === $this->errorMessage) {
            $this->errorMessage = 'Response is missing';
        }
    }

    public function getResponse(): ResponseInterface
    {
        if (null === $this->response) {
            throw new \RuntimeException('Unexpected missing response. Test with the function hasResponse().');
        }

        return $this->response;
    }

    public function hasResponse(): bool
    {
        return null !== $this->response;
    }

    public function isValid(): bool
    {
        return null !== $this->response;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getMimetype(): string
    {
        $mimeType = $this->getResponse()->getHeader('Content-Type');
        if (1 !== \count($mimeType)) {
            throw new \RuntimeException('Unexpected number of mime-type headers %d', \count($mimeType));
        }

        return $mimeType[0];
    }

    public function getStream(): StreamInterface
    {
        return $this->getResponse()->getBody();
    }

    public function isHtml(): bool
    {
        foreach (['text/html', 'text/xml', 'application/xhtml+xml', 'application/xml'] as $mimeType) {
            if (0 === \strpos($this->getMimetype(), $mimeType)) {
                return true;
            }
        }

        return false;
    }
}
