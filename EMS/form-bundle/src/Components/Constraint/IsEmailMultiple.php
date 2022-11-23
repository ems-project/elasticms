<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

final class IsEmailMultiple extends Constraint
{
    public string $message = 'At least one email "{{string}}" is invalid.';
}
