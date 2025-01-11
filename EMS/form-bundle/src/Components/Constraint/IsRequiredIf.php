<?php

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsRequiredIf extends Constraint
{
    public ?string $expression = null;
    public string $message = 'This value should not be blank.';

    /** @return string[] */
    #[\Override]
    public function getRequiredOptions(): array
    {
        return ['expression'];
    }
}
