<?php

namespace EMS\CoreBundle\Entity\Form;

use EMS\CommonBundle\Helper\EmsFields;

class AssetEntity
{
    private string $filename;
    private string $hash;
    /** @var array<string, mixed> */
    private array $config;

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getFile(): array
    {
        return [
            EmsFields::CONTENT_FILE_NAME_FIELD => $this->filename,
            EmsFields::CONTENT_FILE_HASH_FIELD => $this->hash,
            EmsFields::CONTENT_MIME_TYPE_FIELD => $this->config[EmsFields::CONTENT_MIME_TYPE_FIELD_] ?? 'application/bin',
        ];
    }
}
