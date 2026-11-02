<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use EMS\FormBundle\FormConfig\ValidationConfig;

abstract class AbstractValidation implements ValidationInterface
{
    /** @var mixed|null */
    protected $value;
    protected ?string $fieldLabel;
    protected ?string $message;

    public function __construct(ValidationConfig $config)
    {
        $this->value = $config->getValue();
        $this->fieldLabel = $config->getFieldLabel();
        $this->message = $config->getMessage();
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
