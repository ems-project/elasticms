<?php

declare(strict_types=1);

namespace EMS\FormBundle\FormConfig;

abstract class AbstractFormConfig
{
    /** @var ElementInterface[] */
    private array $elements = [];

    public function __construct(private readonly string $id, private readonly string $locale, private readonly string $translationDomain, private string $name = '')
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getElementByName(string $name): ?ElementInterface
    {
        foreach ($this->elements as $elementName => $element) {
            if ($elementName === $name) {
                return $element;
            }

            if ($element instanceof SubFormConfig) {
                $subElement = $element->getElementByName($name);

                if ($subElement instanceof ElementInterface) {
                    return $subElement;
                }
            }
        }

        return null;
    }

    /**
     * @return ElementInterface[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    public function addElement(ElementInterface $element): void
    {
        $this->elements[$element->getName()] = $element;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getTranslationDomain(): string
    {
        return $this->translationDomain;
    }
}
