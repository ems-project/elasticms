<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Entity;

use EMS\Helpers\Standard\DateTime;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Log implements EntityInterface
{
    use CreatedModifiedTrait;

    private UuidInterface $id;
    private string $message;
    /** @var array<mixed> */
    private array $context = [];
    private ?string $ouuid = null;
    private int $level;
    private string $levelName;
    private string $channel;
    /** @var array<mixed> */
    private array $extra = [];
    private string $formatted;
    private ?string $username = null;
    private ?string $impersonator = null;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->created = DateTime::create('now');
        $this->modified = DateTime::create('now');
    }

    #[\Override]
    public function getId(): string
    {
        return $this->id->toString();
    }

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
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
