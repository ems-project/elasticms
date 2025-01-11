<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

final class IsBirthDate extends Constraint
{
    public string $age = 'now';
    public string $message = 'The date must be in the past.';
    public string $messageAge = 'The date must be earlier than "{{age}}".';

    /** @return string[] */
    #[\Override]
    public function getRequiredOptions(): array
    {
        return ['age'];
    }
}
