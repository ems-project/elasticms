<?php

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class Max extends AbstractValidation
{
    public function getHtml5AttributeName(): string
    {
        return 'max';
    }

    public function getConstraint(): Constraint
    {
        return new LessThanOrEqual($this->value);
    }
}
