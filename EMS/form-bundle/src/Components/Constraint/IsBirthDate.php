<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

final class IsBirthDate extends Constraint
{
    public string $age;
    public string $message;
    public string $messageAge;

    public function __construct(
        ?string $age = null,
        ?string $message = null,
        ?string $messageAge = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        $this->age = $age ?? 'now';
        $this->message = $message ?? 'The date must be in the past.';
        $this->messageAge = $messageAge ?? 'The date must be earlier than "{{age}}".';

        parent::__construct(groups: $groups, payload: $payload);
    }
}
