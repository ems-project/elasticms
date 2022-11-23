<?php

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsCompanyNumberMultiple;
use Symfony\Component\Validator\Constraint;

class CompanyNumberMultiple extends AbstractValidation
{
    public function getConstraint(): Constraint
    {
        return new IsCompanyNumberMultiple($this->value);
    }

    public function getHtml5Attribute(): array
    {
        return [];
    }
}
