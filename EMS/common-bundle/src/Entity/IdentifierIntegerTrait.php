<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Entity;

use EMS\Helpers\Standard\Type;

trait IdentifierIntegerTrait
{
    private int|string|null $id = null;

    public function getId(): int
    {
        return Type::integer((int) $this->id);
    }

    public function hasId(): bool
    {
        return null !== $this->id;
    }
}
