<?php

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsRequiredIf;
use Symfony\Component\Validator\Constraint;

class RequiredIf extends AbstractValidation
{
    public function getConstraint(): Constraint
    {
        return new IsRequiredIf(['expression' => $this->value]);
    }
}
