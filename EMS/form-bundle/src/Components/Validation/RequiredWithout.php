<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsRequiredWithout;
use Symfony\Component\Validator\Constraint;

class RequiredWithout extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        $constraint = new IsRequiredWithout(['otherField' => $this->value]);
        if ($this->message) {
            $constraint->message = $this->message;
        }

        return $constraint;
    }
}
