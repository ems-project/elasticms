<?php

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceSelect extends AbstractField
{
    public function getHtmlClass(): string
    {
        return 'choice-select';
    }

    public function getFieldClass(): string
    {
        return ChoiceType::class;
    }

    public function getOptions(): array
    {
        $options = parent::getOptions();
        $options['placeholder'] = $this->config->getChoicePlaceholder();
        $options['choices'] = $this->config->getChoiceList();
        $options['expanded'] = false;
        $options['multiple'] = false;

        return $options;
    }
}
