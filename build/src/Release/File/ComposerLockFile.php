<?php

declare(strict_types=1);

namespace Build\Release\File;

use EMS\Helpers\File\File;
use EMS\Helpers\Standard\DateTime;
use EMS\Helpers\Standard\Json;

class ComposerLockFile
{
    /** @var array<string, ComposerPackage> */
    private array $packages;

    private function __construct(string $directory)
    {
        $contents = Json::decode(File::fromFilename($directory.'/composer.lock')->getContents());

        $this->setPackages($contents['packages'] ?? []);
    }

    public static function create(string $directory): self
    {
        return new self($directory);
    }

    public function getPackage(string $name): ?ComposerPackage
    {
        return $this->packages[$name] ?? null;
    }

    /**
     * @param array<mixed> $packages
     */
    private function setPackages(array $packages): void
    {
        foreach ($packages as $package) {
            $this->packages[(string) $package['name']] = new ComposerPackage(
                name: $package['name'],
                version: $package['version'],
                sha: $package['source']['reference'],
                date: DateTime::createFromFormat($package['time'])
            );
        }
    }
}
