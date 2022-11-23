<?php

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsRequiredWithout;
use Symfony\Component\Validator\Constraint;

class RequiredWithout extends AbstractValidation
{
    public function getConstraint(): Constraint
    {
        return new IsRequiredWithout(['otherField' => $this->value]);
    }
}
