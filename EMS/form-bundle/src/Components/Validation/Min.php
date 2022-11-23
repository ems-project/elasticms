<?php

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class Min extends AbstractValidation
{
    public function getHtml5AttributeName(): string
    {
        return 'min';
    }

    public function getConstraint(): Constraint
    {
        return new GreaterThanOrEqual($this->value);
    }
}
