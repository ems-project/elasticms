<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

final class IsEmailMultiple extends Constraint
{
    public string $message;

    public function __construct(
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        $this->message = $message ?? 'At least one email "{{string}}" is invalid.';

        parent::__construct(groups: $groups, payload: $payload);
    }
}
