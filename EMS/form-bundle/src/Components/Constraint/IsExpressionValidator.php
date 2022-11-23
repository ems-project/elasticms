<?php

namespace EMS\FormBundle\Components\Constraint;

use EMS\CommonBundle\Contracts\ExpressionServiceInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsExpressionValidator extends ConstraintValidator
{
    /** @var ExpressionServiceInterface */
    private $expressionService;

    public function __construct(ExpressionServiceInterface $expressionService)
    {
        $this->expressionService = $expressionService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsExpression) {
            throw new UnexpectedTypeException($constraint, IsExpression::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->evaluate($constraint->expression)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function evaluate(?string $expression): bool
    {
        if (null === $expression) {
            return true;
        }

        /** @var FormInterface<FormInterface> $form */
        $form = $this->context->getRoot();

        return $this->expressionService->evaluateToBool($expression, [
            'data' => $form->getData(),
        ]);
    }
}
