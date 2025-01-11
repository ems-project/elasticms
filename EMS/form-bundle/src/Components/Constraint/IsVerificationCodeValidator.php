<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Constraint;

use EMS\FormBundle\Service\Confirmation\ConfirmationService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsVerificationCodeValidator extends ConstraintValidator
{
    public function __construct(private readonly ConfirmationService $confirmationService)
    {
    }

    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || !$constraint instanceof IsVerificationCode) {
            return;
        }

        if (null === $confirmValue = $this->getConfirmValue($constraint)) {
            return;
        }

        /** @var FormInterface<FormInterface> $field */
        $field = $this->context->getObject();
        $verificationCode = $this->confirmationService->getVerificationCode($field->getName(), $confirmValue);

        if (null === $verificationCode) {
            $this->context->addViolation($constraint->messageMissing);

            return;
        }

        if ($verificationCode !== (string) $value) {
            $this->context->addViolation($constraint->message, ['{{code}}' => $value]);
        }
    }

    private function getConfirmValue(IsVerificationCode $constraint): ?string
    {
        /** @var FormInterface<FormInterface> $form */
        $form = $this->context->getRoot();

        if (!$form instanceof FormInterface) {
            return null;
        }

        /** @var mixed $data */
        $data = $form->getData();

        if (!\is_array($data)) {
            return null;
        }

        return $this->getFieldData($data, $constraint->field);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getFieldData(array $data, ?string $field): ?string
    {
        if (null === $field) {
            return null;
        }

        foreach ($data as $key => $value) {
            if ($key === $field) {
                return $value;
            }

            if (\is_array($value)) {
                if (null !== $subValue = $this->getFieldData($value, $field)) {
                    return $subValue;
                }
            }
        }

        return null;
    }
}
