<?php

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsVerificationCode;
use Symfony\Component\Validator\Constraint;

class VerificationCode extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        return new IsVerificationCode(['field' => $this->getField()]);
    }

    public function getField(): string
    {
        return $this->value;
    }

    #[\Override]
    public function getHtml5Attribute(): array
    {
        return [];
    }
}
