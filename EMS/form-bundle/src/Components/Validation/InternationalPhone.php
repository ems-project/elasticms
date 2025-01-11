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
        return new IsInternationalPhoneNumber($this->value);
    }
}
