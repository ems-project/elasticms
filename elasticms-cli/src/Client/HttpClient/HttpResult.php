<?php

declare(strict_types=1);

namespace App\CLI\Client\HttpClient;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Mime\MimeTypes;

class HttpResult
{
    public function __construct(private readonly ?ResponseInterface $response, private ?string $errorMessage = null)
    {
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
        if (!empty($mimeType)) {
            return $mimeType[0];
        }

        $filename = \tempnam(\sys_get_temp_dir(), 'guess-mime-type');

        if ($filename) {
            $resource = \fopen($filename, 'w');
            if ($resource) {
                $handler = $this->getResponse()->getBody();
                if (0 !== $handler->tell()) {
                    $handler->rewind();
                }

                while (!$handler->eof()) {
                    \fwrite($resource, $handler->read(1024 * 1024));
                }
                \fclose($resource);

                $mimeTypes = new MimeTypes();

                return $mimeTypes->guessMimeType($filename) ?? 'application/bin';
            }
        }

        return 'application/bin';
    }

    public function getStream(): StreamInterface
    {
        return $this->getResponse()->getBody();
    }

    public function isHtml(): bool
    {
        foreach (['text/html', 'application/xhtml+xml'] as $mimeType) {
            if (\str_starts_with($this->getMimetype(), $mimeType)) {
                return true;
            }
        }

        return false;
    }
}
