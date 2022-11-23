<?php

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

class Required extends AbstractValidation
{
    public function getHtml5AttributeName(): string
    {
        return 'required';
    }

    public function getConstraint(): Constraint
    {
        return new NotBlank();
    }

    public function getHtml5Attribute(): array
    {
        return []; // Symfony Forms handles this case.
    }
}
