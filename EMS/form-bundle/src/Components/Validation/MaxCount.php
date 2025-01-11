<?php

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Count;

class MaxCount extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        return new Count(['max' => $this->value]);
    }
}
