<?php

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;

class MinCount extends AbstractValidation
{
    public function getConstraint(): Constraint
    {
        return new Constraints\Count(['min' => $this->value]);
    }
}
