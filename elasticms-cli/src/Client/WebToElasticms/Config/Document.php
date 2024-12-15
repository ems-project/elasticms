<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Config;

use EMS\CommonBundle\Helper\Url;

class Document
{
    /** @var WebResource[] */
    private array $resources;
    private string $type;
    private ?string $ouuid = null;
    /** @var mixed[] */
    private array $defaultData = [];

    /**
     * @return WebResource[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * @param WebResource[] $resources
     */
    public function setResources(array $resources): void
    {
        $this->resources = $resources;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getOuuid(): string
    {
        if (null !== $this->ouuid) {
            return $this->ouuid;
        }
        $resources = $this->getResources();
        if (\count($resources) < 1) {
            throw new \RuntimeException('Document without resource nor ouuid');
        }
        $this->ouuid = \sha1($resources[0]->getUrl());

        return $this->ouuid;
    }

    public function setOuuid(?string $ouuid): void
    {
        $this->ouuid = $ouuid;
    }

    public function hasResourceFor(string $locale): bool
    {
        foreach ($this->resources as $resource) {
            if ($resource->getLocale() === $locale) {
                return true;
            }
        }

        return false;
    }

    public function getResourceFor(string $locale): ?string
    {
        foreach ($this->resources as $resource) {
            if ($resource->getLocale() === $locale) {
                return $resource->getUrl();
            }
        }

        return null;
    }

    public function getResourcePathFor(string $locale): ?string
    {
        foreach ($this->resources as $resource) {
            if ($resource->getLocale() === $locale) {
                $url = new Url($resource->getUrl());

                return $url->getPath();
            }
        }

        return null;
    }

    public function addResource(WebResource $param): void
    {
        $this->resources[] = $param;
    }

    /**
     * @return mixed[]
     */
    public function getDefaultData(): array
    {
        return $this->defaultData;
    }

    /**
     * @param mixed[] $defaultData
     */
    public function setDefaultData(array $defaultData): void
    {
        $this->defaultData = $defaultData;
    }
}
