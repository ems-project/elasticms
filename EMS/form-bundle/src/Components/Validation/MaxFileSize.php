<?php

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\File;

class MaxFileSize extends AbstractValidation
{
    public function getHtml5Attribute(): array
    {
        /** @var File */
        $constraint = $this->getConstraint();

        return [
            'data-maxfilesize' => $constraint->maxSize,
        ];
    }

    public function getConstraint(): Constraint
    {
        return new File(['maxSize' => $this->value]);
    }
}
