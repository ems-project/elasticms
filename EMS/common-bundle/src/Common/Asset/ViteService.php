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
    /** @var array<string, array<string, array{file: string, name: string, css: ?string[]}>> */
    private array $manifests = [];
    private ?bool $devServerRunning = null;

    public const string FILE = '.vite/manifest.json';

    public function __construct(
        private readonly StorageManager $storageManager,
        private readonly HttpClientInterface $httpClient,
        private readonly ?string $devServerUrl = null,
    ) {
    }

    public function loadManifestFromDirectory(string $directory): string
    {
        if (isset($this->manifests[$directory])) {
            return $directory;
        }

        $path = $directory.DIRECTORY_SEPARATOR.self::FILE;
        $this->manifests[$directory] = \file_exists($path) ? Json::decode(File::fromFilename($path)->getContents()) : [];

        return $directory;
    }

    public function loadManifestFromEmsArchive(string $hash): string
    {
        if (isset($this->manifests[$hash])) {
            return $hash;
        }

        try {
            $jsonManifest = $this->storageManager->getStreamFromArchive($hash, self::FILE)->getStream()->getContents();
            $this->manifests[$hash] = Json::decode($jsonManifest);
        } catch (\Throwable) {
        }

        return $hash;
    }

    public function devPath(string $path): ?string
    {
        if (!$this->isDevServerRunning() || \str_ends_with($path, '.css')) {
            return null;
        }

        return \sprintf('%s/%s', \rtrim(Type::string($this->devServerUrl), '/'), \ltrim($path, '/'));
    }

    public function path(string $path, string $manifestId): string
    {
        $manifest = $this->manifests[$manifestId] ?? [];

        if ([] === $manifest) {
            return $path;
        }

        if (\preg_match('/(?<path>.*\.(js|ts|cjs))(\.(?<index>[0-9]+))?\.css$/', $path, $matches) > 0
            && isset($manifest[$matches['path']]['css'][$matches['index'] ?? 0])) {
            return $manifest[$matches['path']]['css'][$matches['index'] ?? 0];
        }

        return $manifest[$path]['file'] ?? $path;
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
