<?php

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsNissInsz;
use Symfony\Component\Validator\Constraint;

class NissInsz extends AbstractValidation
{
    public function getConstraint(): Constraint
    {
        return new IsNissInsz($this->value);
    }

    public function getHtml5Attribute(): array
    {
        return [];
    }
}
