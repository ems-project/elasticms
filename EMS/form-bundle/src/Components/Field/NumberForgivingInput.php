<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\ValueObject\NumberValue;

class NumberForgivingInput extends AbstractForgivingNumberField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'number-forgiving-input';
    }

    #[\Override]
    public function getTransformerClasses(): array
    {
        return [NumberValue::class];
    }
}
