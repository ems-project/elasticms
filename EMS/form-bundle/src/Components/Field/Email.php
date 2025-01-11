<?php

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\EmailType;

class Email extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'email';
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return EmailType::class;
    }
}
