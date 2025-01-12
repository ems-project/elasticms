<?php

declare(strict_types=1);

namespace EMS\FormBundle\FormConfig;

use EMS\FormBundle\Components\Form\SubFormType;

class SubFormConfig extends AbstractFormConfig implements ElementInterface
{
    public function __construct(string $id, string $locale, string $translationDomain, string $name, private readonly string $label)
    {
        parent::__construct($id, $locale, $translationDomain, $name);
    }

    #[\Override]
    public function getClassName(): string
    {
        return SubFormType::class;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
