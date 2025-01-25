<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class Submit extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'submit';
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return SubmitType::class;
    }

    #[\Override]
    public function getOptions(): array
    {
        return [
            'attr' => $this->getAttributes(),
            'label' => $this->config->getLabel(),
            'translation_domain' => false,
            'ems_config_id' => $this->config->getId(),
            'ems_translation_domain' => $this->config->getParentForm()->getTranslationDomain(),
            'ems_meta' => $this->config->getMeta(),
        ];
    }
}
