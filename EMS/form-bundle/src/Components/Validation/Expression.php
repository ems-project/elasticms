<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsExpression;
use Symfony\Component\Validator\Constraint;

class Expression extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        $constraint = new IsExpression(['expression' => $this->value]);
        if ($this->message) {
            $constraint->message = $this->message;
        }

        return $constraint;
    }
}
