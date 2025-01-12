<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

class Required extends AbstractValidation
{
    #[\Override]
    public function getHtml5AttributeName(): string
    {
        return 'required';
    }

    #[\Override]
    public function getConstraint(): Constraint
    {
        return new NotBlank();
    }

    #[\Override]
    public function getHtml5Attribute(): array
    {
        return []; // Symfony Forms handles this case.
    }
}
