<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceSelectMultiple extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'select-multiple';
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
        $options['data'] = [$this->config->getDefaultValue()];
        $options['expanded'] = false;
        $options['multiple'] = true;

        return $options;
    }
}
