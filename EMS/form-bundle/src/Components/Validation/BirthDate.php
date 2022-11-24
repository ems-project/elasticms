<?php

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsBirthDate;
use Symfony\Component\Validator\Constraint;

class BirthDate extends AbstractValidation
{
    public function getConstraint(): Constraint
    {
        return new IsBirthDate(['age' => $this->value]);
    }
}
