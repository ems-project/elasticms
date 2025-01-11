<?php

namespace EMS\FormBundle\Components\Constraint;

use EMS\FormBundle\Components\ValueObject\BelgiumOnssRszNumber;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsOnssRszValidator extends AbstractConstraintValidator
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
        if (!$constraint instanceof IsOnssRsz) {
            throw new UnexpectedTypeException($constraint, IsOnssRsz::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!$this->isOnssRsz($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{string}}', $value)
                ->addViolation();
        }
    }

    /**
     * Nsso number must be 9 or 10 digits.
     */
    private function isOnssRsz(string $nsso): bool
    {
        return $this->canCreateClass(BelgiumOnssRszNumber::class, $nsso);
    }
}
