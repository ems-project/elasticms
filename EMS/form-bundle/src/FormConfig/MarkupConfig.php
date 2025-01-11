<?php

namespace EMS\FormBundle\FormConfig;

use EMS\FormBundle\Components\Form\MarkupType;

class MarkupConfig implements ElementInterface
{
    /**
     * @param mixed[] $meta
     */
    public function __construct(private readonly string $id, private readonly string $name, private readonly string $markup, private array $meta = [])
    {
    }

    #[\Override]
    public function getId(): string
    {
        return $this->id;
    }

    #[\Override]
    public function getClassName(): string
    {
        return MarkupType::class;
    }

    public function getMarkup(): string
    {
        return $this->markup;
    }

    #[\Override]
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
