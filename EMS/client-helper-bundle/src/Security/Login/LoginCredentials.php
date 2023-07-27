<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Login;

use EMS\Helpers\Standard\Type;

class LoginCredentials
{
    public ?string $username;
    public ?string $password;

    public function giveUsername(): string
    {
        return Type::string($this->username);
    }

    public function givePassword(): string
    {
        return Type::string($this->password);
    }
}
