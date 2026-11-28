<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

class IsOnssRsz extends Constraint
{
    public string $message;

    public function __construct(
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        $this->message = $message ?? 'The NSSO number "{{string}}" is invalid.';

        parent::__construct(groups: $groups, payload: $payload);
    }
}
