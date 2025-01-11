<?php

namespace EMS\FormBundle\Components\Constraint;

use EMS\FormBundle\Components\ValueObject\BelgiumPhoneNumber;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsBelgiumPhoneNumberValidator extends AbstractConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    #[\Override]
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsBelgiumPhoneNumber) {
            throw new UnexpectedTypeException($constraint, IsBelgiumPhoneNumber::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!$this->isBelgiumPhoneNumber($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{string}}', $value)
                ->addViolation();
        }
    }

    private function isBelgiumPhoneNumber(string $phone): bool
    {
        return $this->canCreateClass(BelgiumPhoneNumber::class, $phone);
    }
}
