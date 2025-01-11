<?php

namespace EMS\FormBundle\Components\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsRequiredWithoutValidator extends AbstractConstraintValidator
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
        if (!$constraint instanceof IsRequiredWithout) {
            throw new UnexpectedTypeException($constraint, IsRequiredWithout::class);
        }

        if (\is_null($constraint->otherField)) {
            throw new \InvalidArgumentException(\sprintf('The %s::$otherField parameter value is not valid.', $constraint::class));
        }

        if (!$this->isRequiredWithout($value, $constraint->otherField)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{otherField}}', $constraint->otherField)
                ->addViolation();
        }
    }

    private function isRequiredWithout(?string $value, string $otherField): bool
    {
        try {
            $otherFieldValue = $this->context->getRoot()->get($otherField)->getData();
        } catch (\Exception) {
            return false;
        }

        if (\is_null($value) && \is_null($otherFieldValue)) {
            return false;
        }

        return true;
    }
}
