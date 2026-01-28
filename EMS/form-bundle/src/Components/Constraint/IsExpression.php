<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsExpression extends Constraint
{
    public function __construct(
        public ?string $expression = null,
        public string $message = 'This value is not valid.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(groups: $groups, payload: $payload);
    }

    /** @return string[] */
    #[\Override]
    public function getRequiredOptions(): array
    {
        return ['expression'];
    }
}
