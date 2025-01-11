<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Local;

use EMS\ClientHelperBundle\Helper\Environment\Environment;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Filesystem\Filesystem;

final class VersionLockFile
{
    private readonly string $filename;
    /** @var array<mixed> */
    private array $lock = [];
    private const string NAME = 'version.lock';

    public function __construct(string $directory)
    {
        $this->filename = $directory.\DIRECTORY_SEPARATOR.self::NAME;
        $content = \file_exists($this->filename) ? \file_get_contents($this->filename) : false;
        $this->lock = Json::decode($content ?: '{}');
    }

    public function addVersion(Environment $environment, string $version): self
    {
        $this->lock['version'][$environment->getBackendUrl()] = $version;

        return $this;
    }

    public function getVersion(Environment $environment): ?string
    {
        return $this->lock['version'][$environment->getBackendUrl()] ?? null;
    }

    public function save(): self
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->filename, Json::encode($this->lock, true));

        return $this;
    }
}
