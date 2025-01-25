<?php

declare(strict_types=1);

namespace App\CLI\Helper\Tika;

use App\CLI\Helper\AsyncResponse;
use EMS\CommonBundle\Helper\Url;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpClient\CurlHttpClient;

class TikaClient
{
    final public const string TIKA_BASE_URL = 'http://localhost:9998/';
    private readonly Url $serverUrl;
    private ?CurlHttpClient $client = null;

    public function __construct(string $serverBaseUrl = self::TIKA_BASE_URL)
    {
        $this->serverUrl = new Url($serverBaseUrl);
    }

    public function meta(StreamInterface $stream, ?string $mimeType): AsyncResponse
    {
        return $this->putAcceptJson('meta', $stream, $mimeType);
    }

    public function text(StreamInterface $stream, ?string $mimeType): AsyncResponse
    {
        return $this->putAcceptText('tika', $stream, $mimeType);
    }

    public function html(StreamInterface $stream, ?string $mimeType): AsyncResponse
    {
        return $this->putAcceptHtml('tika', $stream, $mimeType);
    }

    private function getClient(): CurlHttpClient
    {
        if (null !== $this->client) {
            return $this->client;
        }
        $this->client = new CurlHttpClient();

        return $this->client;
    }

    private function putAcceptText(string $url, StreamInterface $stream, ?string $mimeType): AsyncResponse
    {
        $this->rewind($stream);
        $request = $this->getClient()->request('PUT', $this->serverUrl->getUrl($url), [
            'headers' => [
                'Accept' => 'text/plain',
                'Content-Type' => $mimeType ?? 'application/bin',
            ],
            'body' => $stream->getContents(),
        ]);

        return new AsyncResponse($request);
    }

    private function putAcceptJson(string $url, StreamInterface $stream, ?string $mimeType): AsyncResponse
    {
        $this->rewind($stream);

        $request = $this->getClient()->request('PUT', $this->serverUrl->getUrl($url), [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => $mimeType ?? 'application/bin',
            ],
            'body' => $stream->getContents(),
        ]);

        return new AsyncResponse($request);
    }

    private function putAcceptHtml(string $url, StreamInterface $stream, ?string $mimeType): AsyncResponse
    {
        $this->rewind($stream);
        $request = $this->getClient()->request('PUT', $this->serverUrl->getUrl($url), [
            'headers' => [
                'Accept' => 'text/html',
                'Content-Type' => $mimeType ?? 'application/bin',
            ],
            'body' => $stream->getContents(),
        ]);

        return new AsyncResponse($request);
    }

    private function rewind(StreamInterface $stream): void
    {
        if ($stream->isSeekable() && $stream->tell() > 0) {
            $stream->rewind();
        }
    }
}
