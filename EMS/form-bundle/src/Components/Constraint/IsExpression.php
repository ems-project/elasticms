<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class IsExpression extends Constraint
{
    public string $message;

    #[HasNamedArguments]
    public function __construct(
        public string $expression,
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        $this->message = $message ?? 'This value is not valid.';

        parent::__construct(groups: $groups, payload: $payload);
    }
}
