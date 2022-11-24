<?php

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsOnssRsz extends Constraint
{
    public string $message = 'The NSSO number "{{string}}" is invalid.';
}
