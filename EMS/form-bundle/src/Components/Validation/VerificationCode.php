<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsVerificationCode;
use Symfony\Component\Validator\Constraint;

class VerificationCode extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        $constraint = new IsVerificationCode(field: $this->getField());
        if ($this->message) {
            $constraint->message = $this->message;
        }

        return $constraint;
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
