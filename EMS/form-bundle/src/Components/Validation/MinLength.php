<?php

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;

class MinLength extends AbstractValidation
{
    #[\Override]
    public function getHtml5AttributeName(): string
    {
        return 'minlength';
    }

    #[\Override]
    public function getConstraint(): Constraint
    {
        return new Length(['min' => $this->value]);
    }
}
