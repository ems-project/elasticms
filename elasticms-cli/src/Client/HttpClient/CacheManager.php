<?php

declare(strict_types=1);

namespace App\CLI\Client\HttpClient;

use EMS\CommonBundle\Helper\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\Psr6CacheStorage;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class CacheManager
{
    private const string WEB_TO_ELASTICMS = 'WebToElasticms';
    private readonly Client $client;
    /** @var UrlReport[] */
    private array $cachedReport = [];

    public function __construct(private readonly string $cacheFolder, bool $allowRedirect = true)
    {
        $stack = HandlerStack::create();
        $stack->push(
            new CacheMiddleware(
                new PrivateCacheStrategy(
                    new Psr6CacheStorage(
                        new FilesystemAdapter(self::WEB_TO_ELASTICMS, 0, $cacheFolder)
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
            RequestOptions::TIMEOUT => 60,
            RequestOptions::READ_TIMEOUT => 10,
        ]);
    }

    public function get(string $url): HttpResult
    {
        try {
            return new HttpResult($this->client->get($url));
        } catch (ClientException|RequestException $e) {
            return new HttpResult($e->getResponse(), $e->getMessage());
        }
    }

    public function head(string $url): HttpResult
    {
        try {
            $head = new HttpResult($this->client->head($url, [
                RequestOptions::CONNECT_TIMEOUT => 3,
            ]));
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

    public function clear(): void
    {
        $finder = new Finder();
        $finder->in(\implode(DIRECTORY_SEPARATOR, [$this->cacheFolder, self::WEB_TO_ELASTICMS]));
        if (!$finder->hasResults()) {
            return;
        }
        $filesystem = new Filesystem();
        foreach ($finder as $file) {
            if ($file->isDir() || $file->isFile() || $file->isLink()) {
                $filesystem->remove($file->getFilename());
            }
        }
    }
}
