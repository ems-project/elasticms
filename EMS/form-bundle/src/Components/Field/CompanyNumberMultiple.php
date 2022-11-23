<?php

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\ValueObject\BelgiumCompanyNumberMultiple;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CompanyNumberMultiple extends AbstractForgivingNumberField
{
    public function getHtmlClass(): string
    {
        return 'company-number-multiple';
    }

    public function getFieldClass(): string
    {
        return TextareaType::class;
    }

    public function getTransformerClasses(): array
    {
        return [BelgiumCompanyNumberMultiple::class];
    }
}
