<?php

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\ValueObject\BisNumber;
use EMS\FormBundle\Components\ValueObject\RrNumber;

class NissInsz extends AbstractForgivingNumberField
{
    public function getHtmlClass(): string
    {
        return 'niss-insz';
    }

    public function getTransformerClasses(): array
    {
        return [BisNumber::class, RrNumber::class];
    }
}
