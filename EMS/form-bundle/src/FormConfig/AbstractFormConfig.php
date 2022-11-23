<?php

namespace EMS\FormBundle\FormConfig;

abstract class AbstractFormConfig
{
    /** @var string */
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $locale;
    /** @var ElementInterface[] */
    private $elements = [];
    /** @var string */
    private $translationDomain;

    public function __construct(string $id, string $locale, string $translationDomain, string $name = '')
    {
        $this->id = $id;
        $this->locale = $locale;
        $this->name = $name;
        $this->translationDomain = $translationDomain;
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
