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
        $max = (int) $this->value;
        if ($max <= 0) {
            throw new \RuntimeException(\sprintf('Maximum value should be greater than zero, %s given', $max));
        }

        return new Length(max: $max);
    }
}
