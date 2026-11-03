<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\Components\Constraint\IsOnssRsz;
use Symfony\Component\Validator\Constraint;

class OnssRsz extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        $constraint = new IsOnssRsz($this->value);
        if ($this->message) {
            $constraint->message = $this->message;
        }

        return $constraint;
    }

    #[\Override]
    public function getHtml5Attribute(): array
    {
        return [];
    }
}
