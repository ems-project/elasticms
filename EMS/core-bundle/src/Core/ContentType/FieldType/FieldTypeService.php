<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\ContentType\FieldType;

use Doctrine\Common\Collections\ArrayCollection;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Entity\FieldType;
use EMS\CoreBundle\Repository\FieldTypeRepository;

class FieldTypeService
{
    /** @var ?ArrayCollection<int, FieldType> */
    private ?ArrayCollection $fieldTypes = null;

    public function __construct(
        private readonly FieldTypeRepository $fieldTypeRepository
    ) {
    }

    public function getTree(ContentType $contentType): FieldTypeTreeItem
    {
        return $this->createTreeItem($contentType->getFieldType());
    }

    private function createTreeItem(FieldType $fieldType, FieldTypeTreeItem $parent = null): FieldTypeTreeItem
    {
        $fieldTypeTreeItem = new FieldTypeTreeItem($fieldType, $parent);

        foreach ($this->getChildren($fieldType) as $child) {
            $childTreeItem = $this->createTreeItem($child, $fieldTypeTreeItem);
            $fieldTypeTreeItem->addChild($childTreeItem);
        }

        $fieldTypeTreeItem->orderChildren();

        return $fieldTypeTreeItem;
    }

    /**
     * @return ArrayCollection<int, FieldType>
     */
    private function getChildren(FieldType $fieldType): ArrayCollection
    {
        return $this->getFieldTypes()->filter(fn (FieldType $f) => $f->getParent()?->getId() === $fieldType->getId());
    }

    /**
     * @return ArrayCollection<int, FieldType>
     */
    private function getFieldTypes(): ArrayCollection
    {
        if (null === $this->fieldTypes) {
            $this->fieldTypes = new ArrayCollection($this->fieldTypeRepository->findBy(['deleted' => false]));
        }

        return $this->fieldTypes;
    }
}
