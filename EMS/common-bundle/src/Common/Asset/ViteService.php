<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Asset;

use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\File\File;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ViteService
{
    /** @var array<string, array{file: string, name: string, css: ?string[]}> */
    private array $manifest = [];
    private ?bool $devServerRunning = null;

    public const FILE = '.vite/manifest.json';

    public function __construct(
        private readonly StorageManager $storageManager,
        private readonly HttpClientInterface $httpClient,
        private readonly ?string $devServerUrl = null,
    ) {
    }

    public function loadManifestFromDirectory(string $directory): void
    {
        $path = $directory.DIRECTORY_SEPARATOR.self::FILE;

        if (!$this->hasManifest() && \file_exists($path)) {
            $this->manifest = Json::decode(File::fromFilename($path)->getContents());
        }
    }

    public function loadManifestFromEmsArchive(string $hash): void
    {
        if ($this->hasManifest()) {
            return;
        }

        $jsonManifest = $this->storageManager->getStreamFromArchive($hash, self::FILE)->getStream()->getContents();
        $this->manifest = Json::decode($jsonManifest);
    }

    public function hasManifest(): bool
    {
        return \count($this->manifest) > 0;
    }

    public function devPath(string $path): ?string
    {
        if (!$this->isDevServerRunning() || \str_ends_with($path, '.css')) {
            return null;
        }

        return \sprintf('%s/%s', \rtrim(Type::string($this->devServerUrl), '/'), \ltrim($path, '/'));
    }

    public function path(string $path): string
    {
        if (!$this->hasManifest()) {
            return $path;
        }

        if (\preg_match('/(?<path>.*\.(js|ts|cjs))(\.(?<index>[0-9]+))?\.css$/', $path, $matches) > 0
            && isset($this->manifest[$matches['path']]['css'][$matches['index'] ?? 0])) {
            return $this->manifest[$matches['path']]['css'][$matches['index'] ?? 0];
        }

        return $this->manifest[$path]['file'] ?? $path;
    }

    public function isDevServerRunning(): bool
    {
        if (null === $this->devServerUrl) {
            return $this->devServerRunning = false;
        }

        return $this->devServerRunning ??= $this->pingDevServer();
    }

    public function devServerClient(): string
    {
        return $this->devServerUrl.'/@vite/client';
    }

    private function pingDevServer(): bool
    {
        try {
            $response = $this->httpClient->request('GET', $this->devServerClient(), ['timeout' => 2]);
            $statusCode = $response->getStatusCode();

            return 200 === $statusCode || 404 === $statusCode;
        } catch (\Exception) {
            return false;
        }
    }
}
