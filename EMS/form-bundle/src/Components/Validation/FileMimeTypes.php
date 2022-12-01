<?php

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\File;

class FileMimeTypes extends AbstractValidation
{
    public function getHtml5AttributeName(): string
    {
        return 'accept';
    }

    public function getConstraint(): Constraint
    {
        return new File(['mimeTypes' => \explode(',', (string) $this->value)]);
    }
}
