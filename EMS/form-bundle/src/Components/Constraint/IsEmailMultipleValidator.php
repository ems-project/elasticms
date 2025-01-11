<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use EMS\FormBundle\Components\ValueObject\EmailMultiple;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class IsEmailMultipleValidator extends AbstractConstraintValidator
{
    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsEmailMultiple) {
            throw new UnexpectedTypeException($constraint, IsEmailMultiple::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!$this->isEmailMultiple($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{string}}', $value)
                ->addViolation();
        }
    }

    private function isEmailMultiple(string $emails): bool
    {
        return $this->canCreateClass(EmailMultiple::class, $emails);
    }
}
