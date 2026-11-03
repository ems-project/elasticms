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
        $constraint = new IsCompanyNumberMultiple($this->value);
        if ($this->message) {
            $constraint->message = $this->message;
        }

        return $constraint;
    }

    #[\Override]
    public function getHtml5Attribute(): array
    {
        return [];
    }
}
