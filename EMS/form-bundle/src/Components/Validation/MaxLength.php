<?php

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;

class MaxLength extends AbstractValidation
{
    public function getHtml5AttributeName(): string
    {
        return 'maxlength';
    }

    public function getConstraint(): Constraint
    {
        return new Length(['max' => $this->value]);
    }
}
