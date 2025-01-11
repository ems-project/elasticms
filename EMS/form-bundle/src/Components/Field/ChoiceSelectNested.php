<?php

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\Form\NestedChoiceType;

class ChoiceSelectNested extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'choice-select-nested';
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return NestedChoiceType::class;
    }
}
