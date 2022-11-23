<?php

namespace EMS\FormBundle\FormConfig;

use EMS\FormBundle\Components\Form\SubFormType;

class SubFormConfig extends AbstractFormConfig implements ElementInterface
{
    private string $label;

    public function __construct(string $id, string $locale, string $translationDomain, string $name, string $label)
    {
        parent::__construct($id, $locale, $translationDomain, $name);
        $this->label = $label;
    }

    public function getClassName(): string
    {
        return SubFormType::class;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
