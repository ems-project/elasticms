<?php

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsOnssRsz;
use Symfony\Component\Validator\Constraint;

class OnssRsz extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        return new IsOnssRsz($this->value);
    }

    #[\Override]
    public function getHtml5Attribute(): array
    {
        return [];
    }
}
