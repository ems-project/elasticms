<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\ContentType\FieldType;

use EMS\CoreBundle\Entity\FieldType;

/**
 * @implements \IteratorAggregate<FieldTypeTreeItem>
 */
class FieldTypeTreeItem implements \IteratorAggregate
{
    private FieldTypeTreeItemCollection $children;
    private string $name;

    public function __construct(
        private readonly FieldType $fieldType,
        private readonly ?FieldTypeTreeItem $parent = null
    ) {
        $this->name = $this->fieldType->getName();
        $this->children = new FieldTypeTreeItemCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function addChild(FieldTypeTreeItem $child): self
    {
        $this->children->set($child->fieldType->getOrderKey(), $child);

        return $this;
    }

    public function getChildren(): FieldTypeTreeItemCollection
    {
        return $this->children;
    }

    public function getChildrenRecursive(): FieldTypeTreeItemCollection
    {
        return new FieldTypeTreeItemCollection($this->toArray());
    }

    public function getFieldType(): FieldType
    {
        return $this->fieldType;
    }

    /**
     * @return \Traversable<FieldTypeTreeItem>
     */
    public function getIterator(): \Traversable
    {
        return new \RecursiveArrayIterator($this->toArray());
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return FieldTypeTreeItem[]
     */
    public function getPath(): array
    {
        $path = [$this];

        if ($this->parent) {
            $path = [...$this->parent->getPath(), ...$path];
        }

        return $path;
    }

    public function orderChildren(): void
    {
        $iterator = $this->children->getIterator();
        $iterator->ksort();

        $this->children = new FieldTypeTreeItemCollection(\iterator_to_array($iterator));
    }

    /**
     * @return FieldTypeTreeItem[]
     */
    public function toArray(): array
    {
        $data = [$this];

        foreach ($this->children as $child) {
            $data = [...$data, ...$child->toArray()];
        }

        return $data;
    }
}
