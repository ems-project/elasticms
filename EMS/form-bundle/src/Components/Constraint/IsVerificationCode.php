<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

final class IsVerificationCode extends Constraint
{
    public function __construct(
        public ?string $field = null,
        public string $message = 'The confirmation code "{{code}}" is not valid.',
        public string $messageMissing = 'You have not requested a confirmation code.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(groups: $groups, payload: $payload);
    }

    /** @return string[] */
    #[\Override]
    public function getRequiredOptions(): array
    {
        return ['field'];
    }
}
