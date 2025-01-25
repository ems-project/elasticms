<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsRequiredIf;
use Symfony\Component\Validator\Constraint;

class RequiredIf extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        return new IsRequiredIf(['expression' => $this->value]);
    }
}
