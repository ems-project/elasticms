<?php

namespace EMS\FormBundle\FormConfig;

use EMS\FormBundle\Components\Form\MarkupType;

class MarkupConfig implements ElementInterface
{
    /** @var string */
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $markup;
    /**
     * @var mixed[]
     */
    private array $meta;

    /**
     * @param mixed[] $meta
     */
    public function __construct(string $id, string $name, string $markup, array $meta = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->markup = $markup;
        $this->meta = $meta;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClassName(): string
    {
        return MarkupType::class;
    }

    public function getMarkup(): string
    {
        return $this->markup;
    }

    public function getName(): string
    {
        return $this->name;
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
