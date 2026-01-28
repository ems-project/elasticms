<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

final class IsBirthDate extends Constraint
{
    public function __construct(
        public string $age = 'now',
        public string $message = 'The date must be in the past.',
        public string $messageAge = 'The date must be earlier than "{{age}}".',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(groups: $groups, payload: $payload);
    }

    /** @return string[] */
    #[\Override]
    public function getRequiredOptions(): array
    {
        return ['age'];
    }
}
