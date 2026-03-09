<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class IsRequiredIf extends Constraint
{
    public string $message;

    #[HasNamedArguments]
    public function __construct(
        public string $expression,
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        $this->message = $message ?? 'This value should not be blank.';

        parent::__construct(groups: $groups, payload: $payload);
    }
}
