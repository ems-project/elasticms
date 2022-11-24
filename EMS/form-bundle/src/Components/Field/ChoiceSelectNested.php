<?php

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\Form\NestedChoiceType;

class ChoiceSelectNested extends AbstractField
{
    public function getHtmlClass(): string
    {
        return 'choice-select-nested';
    }

    public function getFieldClass(): string
    {
        return NestedChoiceType::class;
    }
}
