<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsRequiredIf extends Constraint
{
    public function __construct(
        public ?string $expression = null,
        public string $message = 'This value should not be blank.',
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
