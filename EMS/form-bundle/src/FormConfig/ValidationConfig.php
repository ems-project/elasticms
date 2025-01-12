<?php

declare(strict_types=1);

namespace EMS\FormBundle\FormConfig;

class ValidationConfig
{
    private readonly string $className;

    public function __construct(private readonly string $id, private readonly string $name, string $className, private readonly ?string $fieldLabel, private readonly mixed $defaultValue = null, private readonly mixed $value = null)
    {
        if (!\class_exists($className)) {
            throw new \Exception(\sprintf('Error validation class "%s" does not exists!', $className));
        }
        $this->className = $className;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFieldLabel(): ?string
    {
        return $this->fieldLabel;
    }

    /** @return mixed|null */
    public function getValue()
    {
        return $this->value ?? $this->defaultValue;
    }
}
