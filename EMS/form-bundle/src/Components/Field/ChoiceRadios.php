<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceRadios extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'choice-radios';
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
        $options['choices'] = $this->config->getChoiceList();
        $options['expanded'] = true;
        $options['multiple'] = false;
        $options['placeholder'] = false;

        return $options;
    }
}
