<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Entity;

use EMS\CommonBundle\Entity\CreatedModifiedTrait;
use Ramsey\Uuid\UuidInterface;

class Group implements EntityInterface
{
    use CreatedModifiedTrait;

    private UuidInterface $id;
    protected string $name;
    protected ?string $label = null;
    /** @var mixed[] */
    private array $roles = [];

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->modified = new \DateTime();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isLabelDefined(): bool
    {
        return null !== $this->label;
    }

    public function getLabel(): string
    {
        return $this->label ?? '';
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}
