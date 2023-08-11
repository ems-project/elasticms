<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\JsonMenuNested\Config;

use EMS\CommonBundle\Json\JsonMenuNested;
use EMS\CoreBundle\Entity\FieldType;

class JsonMenuNestedNodes
{
    /** @var array<string, JsonMenuNestedNode> */
    private array $nodes = [];

    public function __construct(FieldType $fieldType)
    {
        if (!$fieldType->isJsonMenuNestedEditorField()) {
            throw new \RuntimeException('invalid field');
        }

        $rootNode = JsonMenuNestedNode::fromFieldType($fieldType);
        $this->nodes['root'] = $rootNode;

        $children = $fieldType->getChildren()
            ->filter(fn (FieldType $child) => !$child->isDeleted() && $child->isContainer());

        foreach ($children as $child) {
            $node = JsonMenuNestedNode::fromFieldType($child);
            $this->nodes[$node->type] = $node;
        }
    }

    public function get(JsonMenuNested $item): JsonMenuNestedNode
    {
        return $this->nodes[$item->getType()];
    }
}
