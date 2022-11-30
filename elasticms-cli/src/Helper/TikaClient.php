<?php

namespace App\CLI\Helper;

use App\CLI\Client\WebToElasticms\Helper\Url;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpClient\HttplugClient;

class TikaClient
{
    public const TIKA_BASE_URL = 'http://localhost:9998/';
    private Url $serverUrl;
    private ?HttplugClient $client = null;

    public function __construct(string $serverBaseUrl = self::TIKA_BASE_URL)
    {
        $this->serverUrl = new Url($serverBaseUrl);
    }

    public function meta(StreamInterface $asset): TikaMetaResponse
    {
        return new TikaMetaResponse($this->putAcceptJson('meta', $asset));
    }

    public function text(StreamInterface $asset): AsyncResponse
    {
        return $this->putAcceptText('tika', $asset);
    }

    public function html(StreamInterface $asset): AsyncResponse
    {
        return $this->putAcceptHtml('tika', $asset);
    }

    private function getClient(): HttplugClient
    {
        if (null !== $this->client) {
            return $this->client;
        }
        $this->client = new HttplugClient();

        return $this->client;
    }

    private function putAcceptText(string $url, StreamInterface $asset): AsyncResponse
    {
        $this->rewind($asset);
        $request = $this->getClient()->createRequest('PUT', $this->serverUrl->getUrl($url), [
            'headers' => [
                'Accept' => 'text/plain',
            ],
            'body' => $asset,
        ]);

        return new AsyncResponse($this->getClient()->sendAsyncRequest($request));
    }

    private function putAcceptJson(string $url, StreamInterface $asset): AsyncResponse
    {
        $this->rewind($asset);
        $request = $this->getClient()->createRequest('PUT', $this->serverUrl->getUrl($url), [
            'Accept' => 'application/json',
        ], $asset);

        return new AsyncResponse($this->getClient()->sendAsyncRequest($request));
    }

    private function putAcceptHtml(string $url, StreamInterface $asset): AsyncResponse
    {
        $this->rewind($asset);
        $request = $this->getClient()->createRequest('PUT', $this->serverUrl->getUrl($url), [
            'Accept' => 'text/html',
        ], $asset);

        return new AsyncResponse($this->getClient()->sendAsyncRequest($request));
    }

    private function rewind(StreamInterface $asset): void
    {
        if ($asset->isSeekable() && $asset->tell() > 0) {
            $asset->rewind();
        }
    }
}
