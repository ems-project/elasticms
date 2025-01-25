<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\Form\SendConfirmationType;
use EMS\FormBundle\Components\Validation\VerificationCode;

/**
 * @deprecated SendConfirmation will be removed, use numberType or HiddenType with VerificationCode validator
 */
class SendConfirmation extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'number';
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return SendConfirmationType::class;
    }

    #[\Override]
    public function getOptions(): array
    {
        $options = parent::getOptions();
        $options['token_id'] = $this->config->getId();

        $validation = $this->getVerificationCodeValidation();
        if ($validation) {
            $options['value_field'] = $validation->getField();
        }

        return $options;
    }

    private function getVerificationCodeValidation(): ?VerificationCode
    {
        foreach ($this->getValidations() as $validation) {
            if ($validation instanceof VerificationCode) {
                return $validation;
            }
        }

        return null;
    }
}
