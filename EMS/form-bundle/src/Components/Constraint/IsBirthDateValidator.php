<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use http\Exception\RuntimeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class IsBirthDateValidator extends ConstraintValidator
{
    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsBirthDate) {
            return;
        }

        if (null === $value || '' === $value) {
            return;
        }

        $dateLimit = $this->getDate($constraint->age);
        if ($this->getDate($value)->getTimestamp() < $dateLimit->getTimestamp()) {
            return;
        }

        if (\in_array($constraint->age, ['now', 'today'])) {
            $this->context->buildViolation($constraint->message)->addViolation();

            return;
        }

        $this->context->buildViolation($constraint->messageAge)
            ->setParameter('{{age}}', $dateLimit->modify('+1 day')->format('d/m/Y'))
            ->addViolation()
        ;
    }

    private function getDate(string $dateString): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($dateString);
        } catch (\Exception) {
            throw new RuntimeException(\sprintf('Could not create date from string "%s"', $dateString));
        }
    }
}
