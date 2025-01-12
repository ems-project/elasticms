<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

class IsBelgiumPhoneNumber extends Constraint
{
    public string $message = 'The phone number "{{string}}" is invalid.';
}
