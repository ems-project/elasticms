<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsEmailMultiple;
use Symfony\Component\Validator\Constraint;

final class EmailMultiple extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        $constraint = new IsEmailMultiple($this->value);
        if ($this->message) {
            $constraint->message = $this->message;
        }

        return $constraint;
    }
}
