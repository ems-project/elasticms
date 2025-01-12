<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Admin;

use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\ConfigInterface;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Finder\Finder;

final readonly class ConfigHelper
{
    public const string DEFAULT_FOLDER = 'admin';
    private string $directory;

    public function __construct(private ConfigInterface $config, string $saveFolder)
    {
        $this->directory = \implode(DIRECTORY_SEPARATOR, [$saveFolder, $this->config->getType()]);
        if (!\is_dir($this->directory)) {
            \mkdir($this->directory, 0777, true);
        }
    }

    public function update(): void
    {
        $finder = new Finder();
        $jsonFiles = $finder->in($this->directory)->files()->name('*.json');
        $names = [];
        foreach ($this->config->index() as $name) {
            $jsonFiles->notName($name.'.json');
            $this->save($name, $this->config->get($name));
            $names[] = $name;
        }

        $finder = new Finder();
        $jsonFiles = $finder->in($this->directory)->files()->name('*.json');
        foreach ($jsonFiles as $file) {
            $name = \pathinfo($file->getFilename(), PATHINFO_FILENAME);
            if (!\is_string($name)) {
                throw new \RuntimeException('Unexpected name type');
            }
            if (\in_array($name, $names)) {
                continue;
            }
            \unlink($file->getPathname());
        }
    }

    /**
     * @param mixed[] $config
     */
    public function save(string $name, array $config): void
    {
        \file_put_contents($this->getFilename($name), Json::encode($config, true));
    }

    public function getFilename(string $name): string
    {
        return $this->directory.DIRECTORY_SEPARATOR.$name.'.json';
    }

    /**
     * @return string[]
     */
    public function local(): array
    {
        $finder = new Finder();
        $names = [];

        foreach ($finder->files()->in($this->directory)->name('*.json') as $file) {
            $name = \pathinfo($file->getFilename(), PATHINFO_FILENAME);
            if (!\is_string($name)) {
                throw new \RuntimeException('Unexpected name type');
            }
            $names[] = $name;
        }

        return $names;
    }

    /**
     * @return string[]
     */
    public function remote(): array
    {
        return $this->config->index();
    }

    /**
     * @param  string[] $names
     * @return string[]
     */
    public function needUpdate(array $names): array
    {
        $filtered = [];
        foreach ($names as $name) {
            $content = \file_get_contents($this->getFilename($name));
            if (false === $content) {
                throw new \RuntimeException('Unexpected false content');
            }
            $local = Json::decode($content);
            $remote = $this->config->get($name);
            if ($local === $remote) {
                continue;
            }
            $filtered[] = $name;
        }

        return $filtered;
    }

    /**
     * @param string[] $names
     */
    public function deleteConfigs(array $names): void
    {
        foreach ($names as $name) {
            $this->config->delete($name);
        }
    }

    /**
     * @param string[] $names
     */
    public function updateConfigs(array $names): void
    {
        foreach ($names as $name) {
            $content = \file_get_contents($this->getFilename($name));
            if (false === $content) {
                throw new \RuntimeException('Unexpected false content');
            }
            $local = Json::decode($content);
            $this->config->update($name, $local);
        }
    }
}
