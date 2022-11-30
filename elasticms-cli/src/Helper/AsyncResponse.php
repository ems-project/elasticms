<?php

namespace App\CLI\Helper;

use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Text;
use Http\Promise\Promise as HttplugPromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class AsyncResponse
{
    private HttplugPromiseInterface $promise;
    private ?ResponseInterface $response = null;
    private bool $trimWhiteSpaces;

    public function __construct(HttplugPromiseInterface $promise, bool $trimWhiteSpaces = true)
    {
        $this->promise = $promise;
        $this->trimWhiteSpaces = $trimWhiteSpaces;
    }

    public function getContent(): string
    {
        return $this->trimWhiteSpaces ? Text::superTrim($this->getResponse()->getBody()->getContents()) : $this->getResponse()->getBody()->getContents();
    }

    public function getStream(): StreamInterface
    {
        return $this->getResponse()->getBody();
    }

    /**
     * @return string[]
     */
    public function getJson(): array
    {
        return Json::decode($this->getContent());
    }

    private function getResponse(): ResponseInterface
    {
        if (null !== $this->response) {
            return $this->response;
        }
        $response = $this->promise->wait();
        if (!$response instanceof ResponseInterface) {
            throw new \RuntimeException(\sprintf('Unexpected response type %s', \get_class($response)));
        }
        $this->response = $response;

        return $this->response;
    }
}
