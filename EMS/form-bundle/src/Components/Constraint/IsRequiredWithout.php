<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsRequiredWithout extends Constraint
{
    public ?string $otherField = null;
    public string $message = 'This field is required when {{otherField}} is not present.';
}
