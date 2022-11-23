<?php

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\DateType;

class Date extends AbstractField
{
    public function getHtmlClass(): string
    {
        return 'date';
    }

    public function getFieldClass(): string
    {
        return DateType::class;
    }

    public function getOptions(): array
    {
        $label = $this->config->getLabel() ?? '';
        $options = parent::getOptions();
        $options['label'] = '%label_date% (dd/mm/yyyy)';
        $options['label_translation_parameters'] = ['%label_date%' => $label];
        $options['translation_domain'] = 'validators';
        $options['widget'] = 'single_text';
        $options['input'] = 'string';
        $options['format'] = 'dd/MM/yyyy';
        $options['html5'] = false;

        return $options;
    }
}
