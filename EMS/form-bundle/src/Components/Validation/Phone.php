<?php

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsBelgiumPhoneNumber;
use Symfony\Component\Validator\Constraint;

class Phone extends AbstractValidation
{
    public function getConstraint(): Constraint
    {
        return new IsBelgiumPhoneNumber($this->value);
    }

    public function getHtml5Attribute(): array
    {
        return [];
    }
}
