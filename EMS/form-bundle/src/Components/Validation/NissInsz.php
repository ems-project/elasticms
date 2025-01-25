<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsNissInsz;
use Symfony\Component\Validator\Constraint;

class NissInsz extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        return new IsNissInsz($this->value);
    }

    #[\Override]
    public function getHtml5Attribute(): array
    {
        return [];
    }
}
