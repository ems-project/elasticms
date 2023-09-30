<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Entity;

use EMS\Helpers\Standard\DateTime;

trait CreatedModifiedTrait
{
    private \DateTimeInterface $created;
    private \DateTimeInterface $modified;

    public function updateModified(): void
    {
        $this->modified = DateTime::create('now');
    }

    public function getCreated(): \DateTimeInterface
    {
        return $this->created;
    }

    public function getModified(): \DateTimeInterface
    {
        return $this->modified;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function setModified(\DateTimeInterface $modified): self
    {
        $this->modified = $modified;

        return $this;
    }
}
