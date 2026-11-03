<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsInternationalPhoneNumber;
use Symfony\Component\Validator\Constraint;

final class InternationalPhone extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        $constraint = new IsInternationalPhoneNumber($this->value);
        if ($this->message) {
            $constraint->message = $this->message;
        }

        return $constraint;
    }
}
