<?php

namespace EMS\CoreBundle\Entity\Form;

use EMS\CommonBundle\Helper\EmsFields;

class AssetEntity
{
    private string $filename;
    private string $hash;
    private ?float $x = null;
    private ?float $y = null;
    private ?float $width = null;
    private ?float $height = null;
    private ?float $rotate = null;
    private ?float $scaleX = null;
    private ?float $scaleY = null;
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

    /**
     * @return string[]
     */
    public function getFile(): array
    {
        return [
            EmsFields::CONTENT_FILE_NAME_FIELD => $this->filename,
            EmsFields::CONTENT_FILE_HASH_FIELD => $this->hash,
            EmsFields::CONTENT_MIME_TYPE_FIELD => $this->getMimetype(),
        ];
    }

    public function getX(): ?float
    {
        return $this->x;
    }

    public function setX(?float $x): void
    {
        $this->x = $x;
    }

    public function getY(): ?float
    {
        return $this->y;
    }

    public function setY(?float $y): void
    {
        $this->y = $y;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(?float $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(?float $height): void
    {
        $this->height = $height;
    }

    public function getRotate(): ?float
    {
        return $this->rotate;
    }

    public function setRotate(?float $rotate): void
    {
        $this->rotate = $rotate;
    }

    public function getScaleX(): ?float
    {
        return $this->scaleX;
    }

    public function setScaleX(?float $scaleX): void
    {
        $this->scaleX = $scaleX;
    }

    public function getScaleY(): ?float
    {
        return $this->scaleY;
    }

    public function setScaleY(?float $scaleY): void
    {
        $this->scaleY = $scaleY;
    }

    public function getMimetype(): string
    {
        return $this->config[EmsFields::CONTENT_MIME_TYPE_FIELD_] ?? 'application/bin';
    }

    public function setMimetype(string $mimetype): void
    {
        $this->config[EmsFields::CONTENT_MIME_TYPE_FIELD_] = $mimetype;
    }
}
