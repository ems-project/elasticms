<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class Min extends AbstractValidation
{
    #[\Override]
    public function getHtml5AttributeName(): string
    {
        return 'min';
    }

    #[\Override]
    public function getConstraint(): Constraint
    {
        return new GreaterThanOrEqual($this->value);
    }
}
