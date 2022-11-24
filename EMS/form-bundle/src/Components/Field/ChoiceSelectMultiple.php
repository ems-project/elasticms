<?php

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceSelectMultiple extends AbstractField
{
    public function getHtmlClass(): string
    {
        return 'select-multiple';
    }

    public function getFieldClass(): string
    {
        return ChoiceType::class;
    }

    public function getOptions(): array
    {
        $options = parent::getOptions();
        $options['choices'] = $this->config->getChoiceList();
        $options['data'] = [$this->config->getDefaultValue()];
        $options['expanded'] = false;
        $options['multiple'] = true;

        return $options;
    }
}
