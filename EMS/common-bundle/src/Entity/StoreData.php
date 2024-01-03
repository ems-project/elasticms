<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Entity;

use EMS\Helpers\Standard\DateTime;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class StoreData
{
    use CreatedModifiedTrait;

    private readonly UuidInterface $id;
    private string $key;
    /** @var array<mixed> */
    protected array $data = [];

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
}
