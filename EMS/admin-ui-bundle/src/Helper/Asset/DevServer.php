<?php

declare(strict_types=1);

namespace EMS\AdminUIBundle\Helper\Asset;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DevServer
{
    private ?bool $running = null;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ?string $devServerUrl = null,
    ) {
    }

    public function getPath(string $path): string
    {
        if (null === $this->devServerUrl) {
            throw new \RuntimeException('Dev server URL is not configured');
        }

        return \sprintf('%s/%s', \rtrim($this->devServerUrl, '/'), \ltrim($path, '/'));
    }

    public function isRunning(): bool
    {
        if (null === $this->devServerUrl) {
            return $this->running = false;
        }

        return $this->running ??= $this->pingDevServer();
    }

    private function pingDevServer(): bool
    {
        try {
            $url = $this->getPath('@vite/client');

            $response = $this->httpClient->request('GET', $url, ['timeout' => 2]);
            $statusCode = $response->getStatusCode();

            return 200 === $statusCode || 404 === $statusCode;
        } catch (\Exception) {
            return false;
        }
    }
}
