<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

final class IsVerificationCode extends Constraint
{
    public string $message;
    public string $messageMissing;

    #[HasNamedArguments]
    public function __construct(
        public string $field,
        ?string $message = null,
        ?string $messageMissing = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        $this->message = $message ?? 'The confirmation code "{{code}}" is not valid.';
        $this->messageMissing = $messageMissing ?? 'You have not requested a confirmation code.';

        parent::__construct(groups: $groups, payload: $payload);
    }
}
