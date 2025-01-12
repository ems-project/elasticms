<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Count;

class MinCount extends AbstractValidation
{
    #[\Override]
    public function getConstraint(): Constraint
    {
        return new Count(['min' => $this->value]);
    }
}
