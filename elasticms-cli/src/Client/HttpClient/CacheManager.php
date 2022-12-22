<?php

declare(strict_types=1);

namespace App\CLI\Client\HttpClient;

use App\CLI\Client\WebToElasticms\Helper\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\Psr6CacheStorage;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CacheManager
{
    private readonly Client $client;
    /** @var UrlReport[] */
    private array $cachedReport = [];
    /** @var array<string, string[]> */
    private array $authSite = [];

    public function __construct(private readonly string $cacheFolder, bool $allowRedirect = true)
    {
        $stack = HandlerStack::create();
        $stack->push(
            new CacheMiddleware(
                new PrivateCacheStrategy(
                    new Psr6CacheStorage(
                        new FilesystemAdapter('WebToElasticms', 0, $cacheFolder.DIRECTORY_SEPARATOR.'cache')
                    )
                )
            ),
            'cache'
        );
        $stack->push(new CacheMiddleware(), 'cache');
        $this->client = new Client([
            'handler' => $stack,
            RequestOptions::ALLOW_REDIRECTS => $allowRedirect,
            RequestOptions::CONNECT_TIMEOUT => 10,
        ]);
    }

    public function get(string $url): HttpResult
    {
        try {
            return new HttpResult($this->client->get($url, $this->getHostOption($url)));
        } catch (ClientException|RequestException $e) {
            return new HttpResult($e->getResponse(), $e->getMessage());
        }
    }

    public function head(string $url): HttpResult
    {
        try {
            $head = new HttpResult($this->client->head($url, $this->getHostOption($url, [
                RequestOptions::CONNECT_TIMEOUT => 3,
            ])));
        } catch (ClientException|RequestException $e) {
            $response = $e->getResponse();
            if (null === $response || !\in_array($response->getStatusCode(), [405, 404])) {
                throw $e;
            }

            return $this->get($url);
        }

        return $head;
    }

    public function testUrl(Url $url): UrlReport
    {
        if (isset($this->cachedReport[$url->getUrl()])) {
            return $this->cachedReport[$url->getUrl()];
        }
        $report = $this->generateUrlReport($url);
        $this->cachedReport[$url->getUrl()] = $report;

        return $report;
    }

    private function generateUrlReport(Url $url): UrlReport
    {
        if (!$url->isCrawlable()) {
            return new UrlReport($url, 0, 'Not crawlable URL');
        }
        try {
            $result = $this->head($url->getUrl());
            $report = new UrlReport($url, $result->getResponse()->getStatusCode());
        } catch (ClientException|RequestException $e) {
            $response = $e->getResponse();
            $report = new UrlReport($url, null === $response ? 0 : $response->getStatusCode(), $e->getMessage());
        }

        return $report;
    }

    public function getCacheFolder(): string
    {
        return $this->cacheFolder;
    }

    public function setHostAuth(string $host, string $username, string $password): void
    {
        $this->authSite[$host] = [$username, $password];
    }

    /**
     * @param mixed[] $options
     *
     * @return mixed[]
     */
    private function getHostOption(string $url, array $options = []): array
    {
        $url = new Url($url);

        return \array_merge(\array_filter(['auth' => $this->authSite[$url->getHost()] ?? []]), $options);
    }
}
