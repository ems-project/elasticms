<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;

class MaxLength extends AbstractValidation
{
    #[\Override]
    public function getHtml5AttributeName(): string
    {
        return 'maxlength';
    }

    #[\Override]
    public function getConstraint(): Constraint
    {
        return new Length(['max' => $this->value]);
    }
}
