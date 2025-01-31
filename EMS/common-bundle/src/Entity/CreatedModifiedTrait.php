<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Entity;

trait CreatedModifiedTrait
{
    private \DateTime $created;
    private \DateTime $modified;

    public function updateModified(): void
    {
        $this->modified = new \DateTime('now');
    }

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function getModified(): \DateTime
    {
        return $this->modified;
    }

    public function setCreated(\DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function setModified(\DateTime $modified): self
    {
        $this->modified = $modified;

        return $this;
    }
}
