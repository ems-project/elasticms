<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use EMS\FormBundle\Components\ValueObject\BelgiumCompanyNumber;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsCompanyNumberValidator extends AbstractConstraintValidator
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
        if (!$constraint instanceof IsCompanyNumber) {
            throw new UnexpectedTypeException($constraint, IsCompanyNumber::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!$this->isCompanyNumber($value)) {
            $this->context->buildViolation($constraint->message)
            ->setParameter('{{string}}', $value)
            ->addViolation();
        }
    }

    /**
     * This number need only 10 numbers and must start with 0 or 1.
     */
    private function isCompanyNumber(string $number): bool
    {
        return $this->canCreateClass(BelgiumCompanyNumber::class, $number);
    }
}
