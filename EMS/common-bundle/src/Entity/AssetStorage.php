<?php

namespace EMS\CommonBundle\Entity;

use EMS\Helpers\Standard\DateTime;

class AssetStorage implements EntityInterface
{
    use CreatedModifiedTrait;
    use IdentifierIntegerTrait;

    private ?string $hash = null;
    /**
     * @var string|resource
     * @phpstan-ignore-next-line
     */
    private $contents;
    private ?string $size = null;
    private ?bool $confirmed = null;

    public function __construct()
    {
        $this->created = DateTime::create('now');
        $this->modified = DateTime::create('now');
    }

    public function getHash(): string
    {
        if (null === $this->hash) {
            throw new \RuntimeException('Unexpected null hash');
        }

        return $this->hash;
    }

    public function setHash(string $hash): AssetStorage
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return string|resource
     */
    public function getContents()
    {
        return $this->contents;
    }

    public function setContents(string $contents): AssetStorage
    {
        $this->contents = $contents;

        return $this;
    }

    public function getSize(): int
    {
        if (null === $this->size) {
            throw new \RuntimeException('Unexpected null size');
        }

        return (int) $this->size;
    }

    public function setSize(int $size): AssetStorage
    {
        $this->size = (string) $size;

        return $this;
    }

    public function isConfirmed(): bool
    {
        if (null === $this->confirmed) {
            throw new \RuntimeException('Unexpected null confirmed');
        }

        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): AssetStorage
    {
        $this->confirmed = $confirmed;

        return $this;
    }
}
