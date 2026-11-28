<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsBirthDate;
use Symfony\Component\Validator\Constraint;

class BirthDate extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        $constraint = new IsBirthDate(age: $this->value);
        if ($this->message) {
            $constraint->message = $this->message;
            $constraint->messageAge = $this->message;
        }

        return $constraint;
    }
}
