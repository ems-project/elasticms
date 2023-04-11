<?php

namespace App\CLI\Helper;

use App\CLI\Client\HttpClient\HttpResult;
use App\CLI\Client\WebToElasticms\Helper\Url;
use Symfony\Component\HttpClient\CurlHttpClient;

class TikaClient
{
    final public const TIKA_BASE_URL = 'http://localhost:9998/';
    private readonly Url $serverUrl;
    private ?CurlHttpClient $client = null;

    public function __construct(string $serverBaseUrl = self::TIKA_BASE_URL)
    {
        $this->serverUrl = new Url($serverBaseUrl);
    }

    public function meta(HttpResult $asset): TikaMetaResponse
    {
        return new TikaMetaResponse($this->putAcceptJson('meta', $asset));
    }

    public function text(HttpResult $asset): AsyncResponse
    {
        return $this->putAcceptText('tika', $asset);
    }

    public function html(HttpResult $asset): AsyncResponse
    {
        return $this->putAcceptHtml('tika', $asset);
    }

    private function getClient(): CurlHttpClient
    {
        if (null !== $this->client) {
            return $this->client;
        }
        $this->client = new CurlHttpClient();

        return $this->client;
    }

    private function putAcceptText(string $url, HttpResult $asset): AsyncResponse
    {
        $this->rewind($asset);
        $request = $this->getClient()->request('PUT', $this->serverUrl->getUrl($url), [
            'headers' => [
                'Accept' => 'text/plain',
                'Content-Type' => $asset->getMimetype(),
            ],
            'body' => $asset->getStream()->getContents(),
        ]);

        return new AsyncResponse($request);
    }

    private function putAcceptJson(string $url, HttpResult $asset): AsyncResponse
    {
        $this->rewind($asset);

        $request = $this->getClient()->request('PUT', $this->serverUrl->getUrl($url), [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => $asset->getMimetype(),
            ],
            'body' => $asset->getStream()->getContents(),
        ]);

        return new AsyncResponse($request);
    }

    private function putAcceptHtml(string $url, HttpResult $asset): AsyncResponse
    {
        $this->rewind($asset);
        $request = $this->getClient()->request('PUT', $this->serverUrl->getUrl($url), [
            'headers' => [
                'Accept' => 'text/html',
                'Content-Type' => $asset->getMimetype(),
            ],
            'body' => $asset->getStream()->getContents(),
        ]);

        return new AsyncResponse($request);
    }

    private function rewind(HttpResult $asset): void
    {
        $stream = $asset->getStream();
        if ($stream->isSeekable() && $stream->tell() > 0) {
            $stream->rewind();
        }
    }
}
