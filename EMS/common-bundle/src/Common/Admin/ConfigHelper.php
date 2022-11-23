<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Admin;

use EMS\CommonBundle\Common\Standard\Json;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\ConfigInterface;
use Symfony\Component\Finder\Finder;

final class ConfigHelper
{
    public const DEFAULT_FOLDER = 'admin';
    private string $directory;
    private ConfigInterface $config;

    public function __construct(ConfigInterface $config, string $saveFolder)
    {
        $this->config = $config;
        $this->directory = \implode(DIRECTORY_SEPARATOR, [$saveFolder, $this->config->getType()]);
        if (!\is_dir($this->directory)) {
            \mkdir($this->directory, 0777, true);
        }
    }

    public function update(): void
    {
        $finder = new Finder();
        $jsonFiles = $finder->in($this->directory)->files()->name('*.json');
        foreach ($this->config->index() as $name) {
            $jsonFiles->notName($name.'.json');
            $this->save($name, $this->config->get($name));
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
}
