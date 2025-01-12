<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\File;

class MaxFileSize extends AbstractValidation
{
    #[\Override]
    public function getHtml5Attribute(): array
    {
        /** @var File */
        $constraint = $this->getConstraint();

        return [
            'data-maxfilesize' => $constraint->maxSize,
        ];
    }

    #[\Override]
    public function getConstraint(): Constraint
    {
        return new File(['maxSize' => $this->value]);
    }
}
