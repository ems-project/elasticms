<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsRequiredIfValidator extends ConstraintValidator
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$this->isEmpty($value) || !$constraint instanceof IsRequiredIf) {
            return;
        }

        if ($this->isRequiredIf($constraint->expression)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function isRequiredIf(?string $expression): bool
    {
        if (null === $expression) {
            return true;
        }

        /** @var FormInterface<mixed> $form */
        $form = $this->context->getRoot();
        $values = ['data' => $form->getData()];

        try {
            $expressionLanguage = new ExpressionLanguage();
            $result = $expressionLanguage->evaluate($expression, $values);

            return \is_bool($result) ? $result : false;
        } catch (\Exception $e) {
            $this->logger->error('Required if failed: {message}', [
                'message' => $e->getMessage(),
                'values' => $values,
            ]);

            return false;
        }
    }

    private function isEmpty(mixed $value): bool
    {
        if (null === $value || '' === $value) {
            return true;
        }
        if (!\is_array($value)) {
            return false;
        }

        foreach ($value as $subValue) {
            if (!$this->isEmpty($subValue)) {
                return false;
            }
        }

        return true;
    }
}
