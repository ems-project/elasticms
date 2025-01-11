<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use EMS\FormBundle\Components\ValueObject\InternationalPhoneNumber;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class IsInternationalPhoneNumberValidator extends AbstractConstraintValidator
{
    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsInternationalPhoneNumber) {
            throw new UnexpectedTypeException($constraint, IsInternationalPhoneNumber::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!$this->isInternationalPhoneNumber($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{string}}', $value)
                ->addViolation();
        }
    }

    private function isInternationalPhoneNumber(string $phoneNumber): bool
    {
        return $this->canCreateClass(InternationalPhoneNumber::class, $phoneNumber);
    }
}
