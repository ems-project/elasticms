<?php

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsExpression extends Constraint
{
    public ?string $expression = null;
    public string $message = 'This value is not valid.';

    /** @return string[] */
    #[\Override]
    public function getRequiredOptions(): array
    {
        return ['expression'];
    }
}
