<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceSelect extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'choice-select';
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return ChoiceType::class;
    }

    #[\Override]
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
