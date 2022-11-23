<?php

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\ConstraintValidator;

abstract class AbstractConstraintValidator extends ConstraintValidator
{
    protected function canCreateClass(string $class, string $value): bool
    {
        try {
            new $class($value);

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
