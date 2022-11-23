<?php

namespace App\Helper;

use App\Client\WebToElasticms\Helper\Url;
use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;

class TikaClient
{
    public const TIKA_BASE_URL = 'http://localhost:9998/';
    private Url $serverUrl;
    private ?Client $client = null;

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

    private function getClient(): Client
    {
        if (null !== $this->client) {
            return $this->client;
        }
        $this->client = new Client();

        return $this->client;
    }

    private function putAcceptText(string $url, StreamInterface $asset): AsyncResponse
    {
        $this->rewind($asset);

        return new AsyncResponse($this->getClient()->putAsync($this->serverUrl->getUrl($url), [
            'headers' => [
                'Accept' => 'text/plain',
            ],
            'body' => $asset,
        ],
        ));
    }

    private function putAcceptJson(string $url, StreamInterface $asset): AsyncResponse
    {
        $this->rewind($asset);

        return new AsyncResponse($this->getClient()->putAsync($this->serverUrl->getUrl($url), [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'body' => $asset,
        ],
        ));
    }

    private function putAcceptHtml(string $url, StreamInterface $asset): AsyncResponse
    {
        $this->rewind($asset);

        return new AsyncResponse($this->getClient()->putAsync($this->serverUrl->getUrl($url), [
            'headers' => [
                'Accept' => 'text/html',
            ],
            'body' => $asset,
        ],
        ));
    }

    private function rewind(StreamInterface $asset): void
    {
        if ($asset->isSeekable() && $asset->tell() > 0) {
            $asset->rewind();
        }
    }
}
