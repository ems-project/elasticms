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
        return $this->buildTreeItem($contentType->getFieldType());
    }

    private function buildTreeItem(FieldType $fieldType): FieldTypeTreeItem
    {
        $fieldTypeTreeItem = new FieldTypeTreeItem($fieldType);

        foreach ($this->getChildren($fieldType) as $child) {
            $childTreeItem = $this->buildTreeItem($child)->setParent($fieldTypeTreeItem);
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
            $fieldTypes = $this->fieldTypeRepository->findAllNotDeleted();
            $this->fieldTypes = new ArrayCollection($fieldTypes);
        }

        return $this->fieldTypes;
    }
}
