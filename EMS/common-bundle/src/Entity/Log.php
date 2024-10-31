<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="log_message",indexes={@ORM\Index(name="channel_ouuid_idx", columns={"channel", "ouuid"})})
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Log implements EntityInterface
{
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
     * @ORM\Column(name="created", type="datetime")
     */
    private \DateTime $created;

    /**
     * @ORM\Column(name="modified", type="datetime")
     */
    private \DateTime $modified;

    /**
     * @ORM\Column(type="text")
     */
    private string $message;

    /**
     * @var array<mixed>
     *
     * @ORM\Column(type="json")
     */
    private array $context = [];

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $ouuid = null;

    /**
     * @ORM\Column(type="smallint")
     */
    private int $level;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $levelName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $channel;

    /**
     * @var array<mixed>
     *
     * @ORM\Column(type="json")
     */
    private array $extra = [];

    /**
     * @ORM\Column(type="text")
     */
    private string $formatted;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $username = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $impersonator = null;

    /**
     * @ORM\PrePersist
     *
     * @ORM\PreUpdate
     */
    public function updateModified(): void
    {
        $this->modified = new \DateTime();
        if (!isset($this->created)) {
            $this->created = $this->modified;
        }
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function getModified(): \DateTime
    {
        return $this->modified;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return mixed[]
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param mixed[] $context
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function getOuuid(): ?string
    {
        return $this->ouuid;
    }

    public function setOuuid(?string $ouuid): void
    {
        $this->ouuid = $ouuid;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getLevelName(): string
    {
        return $this->levelName;
    }

    public function setLevelName(string $levelName): void
    {
        $this->levelName = $levelName;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @return mixed[]
     */
    public function getExtra(): array
    {
        return $this->extra;
    }

    /**
     * @param mixed[] $extra
     */
    public function setExtra(array $extra): void
    {
        $this->extra = $extra;
    }

    public function getFormatted(): string
    {
        return $this->formatted;
    }

    public function setFormatted(string $formatted): void
    {
        $this->formatted = $formatted;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getImpersonator(): ?string
    {
        return $this->impersonator;
    }

    public function setImpersonator(string $impersonator): void
    {
        $this->impersonator = $impersonator;
    }
}
