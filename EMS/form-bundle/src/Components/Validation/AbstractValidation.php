<?php

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\FormConfig\ValidationConfig;

abstract class AbstractValidation implements ValidationInterface
{
    /** @var mixed|null */
    protected $value;
    protected ?string $fieldLabel;

    public function __construct(ValidationConfig $config)
    {
        $this->value = $config->getValue();
        $this->fieldLabel = $config->getFieldLabel();
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function getHtml5Attribute(): array
    {
        return ('' === $this->getHtml5AttributeName()) ? [] : [$this->getHtml5AttributeName() => $this->value];
    }

    #[\Override]
    public function getHtml5AttributeName(): string
    {
        return '';
    }
}
