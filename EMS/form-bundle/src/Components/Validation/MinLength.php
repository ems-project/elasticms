<?php

declare(strict_types=1);

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
        $min = (int) $this->value;
        if ($min < 0) {
            throw new \RuntimeException(\sprintf('Minimum value should be greater, or equal, than zero, %s given', $min));
        }

        return new Length(min: (int) $min);
    }
}
