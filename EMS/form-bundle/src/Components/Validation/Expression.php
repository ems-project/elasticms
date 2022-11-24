<?php

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsExpression;
use Symfony\Component\Validator\Constraint;

class Expression extends AbstractValidation
{
    public function getConstraint(): Constraint
    {
        return new IsExpression(['expression' => $this->value]);
    }
}
