<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsBelgiumPhoneNumber;
use Symfony\Component\Validator\Constraint;

class Phone extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        $constraint = new IsBelgiumPhoneNumber(message: $this->value);
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
