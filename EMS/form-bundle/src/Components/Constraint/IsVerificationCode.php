<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;

final class IsVerificationCode extends Constraint
{
    public ?string $field = null;
    public string $message = 'The confirmation code "{{code}}" is not valid.';
    public string $messageMissing = 'You have not requested a confirmation code.';

    /** @return string[] */
    #[\Override]
    public function getRequiredOptions(): array
    {
        return ['field'];
    }
}
