<?php

namespace EMS\FormBundle\FormConfig;

class FieldConfig implements ElementInterface
{
    private string $id;
    private string $name;
    private string $type;
    /** @var string[] */
    private array $class = [];
    private string $className;
    private ?string $placeholder = null;
    private ?string $defaultValue = null;
    private ?string $label = null;
    private ?string $help = null;
    /** @var ValidationConfig[] */
    private array $validations = [];
    private ?FieldChoicesConfig $choices = null;
    private AbstractFormConfig $parentForm;
    /** @var mixed[] */
    private array $meta;

    /**
     * @param mixed[] $meta
     */
    public function __construct(string $id, string $name, string $type, string $className, AbstractFormConfig $parentForm, array $meta = [])
    {
        if (!\class_exists($className)) {
            throw new \Exception(\sprintf('Error field class "%s" does not exists!', $className));
        }

        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->className = $className;
        $this->class[] = $name;
        $this->parentForm = $parentForm;
        $this->meta = $meta;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function addClass(string $class): void
    {
        $this->class[] = $class;
    }

    public function addValidation(ValidationConfig $validation): void
    {
        $this->validations[$validation->getName()] = $validation;
    }

    public function hasChoices(): bool
    {
        return ($this->choices instanceof FieldChoicesConfig) && (\count($this->choices->list()) > 0);
    }

    public function getChoicePlaceholder(): ?string
    {
        return $this->choices ? $this->choices->getPlaceHolder() : null;
    }

    /** @return mixed[] */
    public function getChoiceList(): array
    {
        return $this->choices ? $this->choices->list() : [];
    }

    public function getChoices(): ?FieldChoicesConfig
    {
        return $this->choices;
    }

    public function getClass(): string
    {
        $classes = $this->class;
        if (null !== $this->getChoices() && $this->getChoices()->isMultiLevel()) {
            $classes[] = 'dynamic-choice-select';
        }

        return \implode(' ', $classes);
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $classname): void
    {
        $this->className = $classname;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ValidationConfig[]
     */
    public function getValidations(): array
    {
        return $this->validations;
    }

    public function setChoices(FieldChoicesConfig $choices): void
    {
        $this->choices = $choices;
    }

    public function setPlaceholder(string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    public function setDefaultValue(string $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    public function setHelp(?string $help): void
    {
        $this->help = $help;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getParentForm(): AbstractFormConfig
    {
        return $this->parentForm;
    }

    /**
     * @return mixed[]
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param mixed[] $meta
     */
    public function setMeta(array $meta): void
    {
        $this->meta = $meta;
    }
}
