<?php

namespace EMS\CoreBundle\Entity;

use EMS\CommonBundle\Entity\CreatedModifiedTrait;
use EMS\Helpers\Standard\DateTime;

class CacheAssetExtractor
{
    use CreatedModifiedTrait;

    private int $id;
    private string $hash;

    /** @var mixed[]|null */
    private ?array $data = null;

    public function __construct()
    {
        $this->created = DateTime::create('now');
        $this->modified = DateTime::create('now');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array<mixed> $data
     */
    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
