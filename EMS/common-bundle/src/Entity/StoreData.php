<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Entity;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class StoreData
{
    use CreatedModifiedTrait;

    private readonly UuidInterface $id;
    private string $key;

    /** @var array<mixed> */
    protected array $data = [];
    protected ?\DateTimeInterface $expiresAt = null;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->created = new \DateTime('now');
        $this->modified = new \DateTime('now');
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<mixed> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function expiresAt(\DateTime $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function expiresAfter(int $ttl): void
    {
        $this->expiresAt = new \DateTime(\sprintf('%d seconds', $ttl));
    }

    public function isExpired(): bool
    {
        return null !== $this->expiresAt && $this->expiresAt < new \DateTime();
    }
}
