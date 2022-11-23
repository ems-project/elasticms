<?php

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsCompanyNumber;
use Symfony\Component\Validator\Constraint;

class CompanyNumber extends AbstractValidation
{
    public function getConstraint(): Constraint
    {
        return new IsCompanyNumber($this->value);
    }

    public function getHtml5Attribute(): array
    {
        return [];
    }
}
