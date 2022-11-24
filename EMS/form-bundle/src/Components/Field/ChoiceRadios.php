<?php

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceRadios extends AbstractField
{
    public function getHtmlClass(): string
    {
        return 'choice-radios';
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
        $options['multiple'] = false;
        $options['placeholder'] = false;

        return $options;
    }
}
