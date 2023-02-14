<?php

namespace EMS\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Analyzer.
 *
 * @ORM\Table(name="asset_storage")
 *
 * @ORM\Entity(repositoryClass="EMS\CommonBundle\Repository\AssetStorageRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class AssetStorage implements EntityInterface
{
    /**
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="created", type="datetime")
     */
    private ?\DateTime $created = null;

    /**
     * @ORM\Column(name="modified", type="datetime")
     */
    private ?\DateTime $modified = null;

    /**
     * @ORM\Column(name="hash", type="string", length=1024, unique=true)
     */
    private ?string $hash = null;

    /**
     * @var string|resource
     *
     * @ORM\Column(name="contents", type="blob")
     */
    private $contents;

    /**
     * @ORM\Column(name="size", type="bigint")
     */
    private ?int $size = null;

    /**
     * @ORM\Column(name="confirmed", type="boolean", options={"default" : 0})
     */
    private ?bool $confirmed = null;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @ORM\PrePersist
     *
     * @ORM\PreUpdate
     */
    public function updateModified(): void
    {
        $this->modified = new \DateTime();
        if (null === $this->created) {
            $this->created = $this->modified;
        }
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCreated(\DateTime $created): AssetStorage
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): \DateTime
    {
        if (null === $this->created) {
            throw new \RuntimeException('Not yet created');
        }

        return $this->created;
    }

    public function setModified(\DateTime $modified): AssetStorage
    {
        $this->modified = $modified;

        return $this;
    }

    public function getModified(): \DateTime
    {
        if (null === $this->modified) {
            throw new \RuntimeException('Unexpected null modified');
        }

        return $this->modified;
    }

    public function getSize(): int
    {
        if (null === $this->size) {
            throw new \RuntimeException('Unexpected null size');
        }

        return $this->size;
    }

    public function setSize(int $size): AssetStorage
    {
        $this->size = $size;

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
