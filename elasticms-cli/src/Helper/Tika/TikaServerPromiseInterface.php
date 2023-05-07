<?php

namespace App\CLI\Helper\Tika;

use App\CLI\Helper\AsyncResponse;
use Psr\Http\Message\StreamInterface;

class TikaServerPromiseInterface implements TikaPromiseInterface
{
    private AsyncResponse $textRequest;

    public function __construct(string $serverBaseUrl, StreamInterface $stream, ?string $mimeType)
    {
        $this->textRequest = (new TikaClient($serverBaseUrl))->text($stream, $mimeType);
    }

    public function getText(): string
    {
        return $this->textRequest->getContent();
    }
}
