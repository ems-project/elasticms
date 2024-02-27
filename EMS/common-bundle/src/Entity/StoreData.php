<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use EMS\Helpers\Standard\DateTime;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="store_data")
 *
 * @ORM\Entity()
 *
 * @ORM\HasLifecycleCallbacks()
 */
class StoreData
{
    use CreatedModifiedTrait;

    /**
     * @ORM\Id
     *
     * @ORM\Column(type="uuid", unique=true)
     *
     * @ORM\GeneratedValue(strategy="CUSTOM")
     *
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(name="key", type="string", length=2048, unique=true)
     */
    private string $key;

    /**
     * @var mixed[]
     *
     * @ORM\Column(name="data", type="json", nullable=true)
     */
    protected array $data = [];

    /**
     * @ORM\Column(name="expires_at", type="datetime", nullable=true)
     */
    protected ?\DateTimeInterface $expiresAt = null;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->created = DateTime::create('now');
        $this->modified = DateTime::create('now');
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
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param mixed[] $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function expiresAt(\DateTimeInterface $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function expiresAfter(int $ttl): void
    {
        $this->expiresAt = new \DateTimeImmutable(\sprintf('%d seconds', $ttl));
    }

    public function isExpired(): bool
    {
        return null !== $this->expiresAt && $this->expiresAt < new \DateTimeImmutable();
    }
}
