<?php

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\ValueObject\BelgiumCompanyNumber;

class CompanyNumber extends AbstractForgivingNumberField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'company-number';
    }

    #[\Override]
    public function getTransformerClasses(): array
    {
        return [BelgiumCompanyNumber::class];
    }
}
