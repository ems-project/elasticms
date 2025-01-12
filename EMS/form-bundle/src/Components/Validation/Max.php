<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class Max extends AbstractValidation
{
    #[\Override]
    public function getHtml5AttributeName(): string
    {
        return 'max';
    }

    #[\Override]
    public function getConstraint(): Constraint
    {
        return new LessThanOrEqual($this->value);
    }
}
