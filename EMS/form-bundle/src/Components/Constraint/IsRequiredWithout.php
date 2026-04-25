<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

class IsRequiredWithout extends Constraint
{
    public string $message;

    public function __construct(
        public ?string $otherField = null,
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        $this->message = $message ?? 'This field is required when {{otherField}} is not present.';

        parent::__construct(groups: $groups, payload: $payload);
    }
}
