<?php

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsNissInsz extends Constraint
{
    public string $message = 'The social security number "{{string}}" is invalid.';
}
