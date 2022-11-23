<?php

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\ValueObject\NumberValue;

class NumberForgivingInput extends AbstractForgivingNumberField
{
    public function getHtmlClass(): string
    {
        return 'number-forgiving-input';
    }

    public function getTransformerClasses(): array
    {
        return [NumberValue::class];
    }
}
