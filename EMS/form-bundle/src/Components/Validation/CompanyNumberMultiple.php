<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsCompanyNumberMultiple;
use Symfony\Component\Validator\Constraint;

class CompanyNumberMultiple extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        return new IsCompanyNumberMultiple($this->value);
    }

    #[\Override]
    public function getHtml5Attribute(): array
    {
        return [];
    }
}
