<?php

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\TimeType;

class Time extends AbstractField
{
    public function getHtmlClass(): string
    {
        return 'time';
    }

    public function getFieldClass(): string
    {
        return TimeType::class;
    }

    public function getOptions(): array
    {
        $label = $this->config->getLabel() ?? '';
        $options = parent::getOptions();
        $options['label'] = '%label_time% (hh:mm)';
        $options['label_translation_parameters'] = ['%label_time%' => $label];
        $options['translation_domain'] = 'validators';
        $options['widget'] = 'single_text';
        $options['input'] = 'string';
        $options['input_format'] = 'H:i';
        $options['html5'] = false;

        return $options;
    }
}
