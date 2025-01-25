<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\Validation\ValidationInterface;
use EMS\FormBundle\FormConfig\FieldConfig;
use EMS\FormBundle\FormConfig\SubFormConfig;
use EMS\FormBundle\FormConfig\ValidationConfig;
use Symfony\Component\Validator\Constraint;

abstract class AbstractField implements FieldInterface
{
    /** @var ValidationInterface[] */
    private array $validations = [];

    public function __construct(protected FieldConfig $config)
    {
        foreach ($config->getValidations() as $id => $validationConfig) {
            $this->validations[$id] = $this->createValidation($validationConfig);
        }
    }

    /** @return mixed[] */
    #[\Override]
    public function getOptions(): array
    {
        return [
            'attr' => $this->getAttributes(),
            'constraints' => $this->getValidationConstraints(),
            'data' => $this->config->getDefaultValue(),
            'help' => $this->config->getHelp(),
            'label' => $this->config->getLabel(),
            'label_attr' => $this->getLabelAttributes(),
            'required' => $this->isRequired(),
            'translation_domain' => false,
            'ems_config_id' => $this->config->getId(),
            'ems_translation_domain' => $this->config->getParentForm()->getTranslationDomain(),
            'ems_meta' => $this->config->getMeta(),
        ];
    }

    private function isRequired(): bool
    {
        foreach ($this->validations as $validation) {
            if ('required' === $validation->getHtml5AttributeName()) {
                return true;
            }
        }

        return false;
    }

    private function createValidation(ValidationConfig $config): ValidationInterface
    {
        $class = $config->getClassName();
        /** @var ValidationInterface $validation */
        $validation = new $class($config);

        return $validation;
    }

    /** @return string[] */
    protected function getAttributes(): array
    {
        $attributes = \array_merge_recursive($this->getValidationHtml5Attribute(), [
            'class' => [$this->getHtmlClass(), $this->config->getClass()],
        ]);

        $attributes['class'] = \implode(' ', $attributes['class']);
        $attributes['lang'] = $this->config->getParentForm()->getLocale();
        if (null !== $this->config->getPlaceholder() && '' !== $this->config->getPlaceholder()) {
            $attributes['placeholder'] = $this->config->getPlaceholder();
        }

        return $attributes;
    }

    /** @return string[] */
    protected function getLabelAttributes(string $postfix = ''): array
    {
        $parentForm = $this->config->getParentForm();

        return [
            'id' => \sprintf(
                'form_%s%s%s_label',
                $parentForm instanceof SubFormConfig ? \sprintf('%s_', $parentForm->getName()) : '',
                $this->config->getName(),
                $postfix
            ),
        ];
    }

    /** @return ValidationInterface[] */
    protected function getValidations(): array
    {
        return $this->validations;
    }

    /** @return Constraint[] */
    protected function getValidationConstraints(): array
    {
        return \array_map(fn (ValidationInterface $validation) => $validation->getConstraint(), $this->validations);
    }

    /** @return array<array<string>> */
    private function getValidationHtml5Attribute(): array
    {
        $html5Attributes = [];

        foreach ($this->validations as $validation) {
            $html5Attributes = [...$html5Attributes, ...$validation->getHtml5Attribute()];
        }

        return $html5Attributes;
    }
}
