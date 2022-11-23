<?php

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email as EmailValidation;

class Email extends AbstractValidation
{
    public function getHtml5AttributeName(): string
    {
        return 'email';
    }

    public function getConstraint(): Constraint
    {
        return new EmailValidation(['mode' => EmailValidation::VALIDATION_MODE_HTML5]);
    }

    public function getHtml5Attribute(): array
    {
        return []; // Symfony framework.validation config handles this case.
    }
}
