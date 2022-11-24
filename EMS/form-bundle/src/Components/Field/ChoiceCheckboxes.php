<?php

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceCheckboxes extends AbstractField
{
    public function getHtmlClass(): string
    {
        return 'choice-checkboxes';
    }

    public function getFieldClass(): string
    {
        return ChoiceType::class;
    }

    public function getOptions(): array
    {
        $options = parent::getOptions();
        $options['choices'] = $this->config->getChoiceList();
        $options['expanded'] = true;
        $options['multiple'] = true;

        return $options;
    }
}
