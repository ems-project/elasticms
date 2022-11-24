<?php

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\ValueObject\BelgiumCompanyNumber;

class CompanyNumber extends AbstractForgivingNumberField
{
    public function getHtmlClass(): string
    {
        return 'company-number';
    }

    public function getTransformerClasses(): array
    {
        return [BelgiumCompanyNumber::class];
    }
}
