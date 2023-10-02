<?php

namespace EMS\CoreBundle\Entity;

use EMS\CommonBundle\Entity\CreatedModifiedTrait;
use EMS\Helpers\Standard\DateTime;

class AuthToken
{
    use CreatedModifiedTrait;

    private int $id;
    private string $value;

    public function __construct(private UserInterface $user)
    {
        $this->value = \base64_encode(\random_bytes(50));

        $this->created = DateTime::create('now');
        $this->modified = DateTime::create('now');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setUser(UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
