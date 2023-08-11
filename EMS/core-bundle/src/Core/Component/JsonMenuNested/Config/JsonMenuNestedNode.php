<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\JsonMenuNested\Config;

use EMS\CoreBundle\Entity\FieldType;

class JsonMenuNestedNode
{
    /**
     * @param string[] $deny
     */
    public function __construct(
        public readonly string $type,
        public readonly string $role,
        public readonly ?string $icon,
        public readonly array $deny,
        public readonly bool $leaf,
    ) {
    }

    public static function fromFieldType(FieldType $fieldType): self
    {
        return new self(
            $fieldType->getName(),
            $fieldType->getMinimumRole(),
            $fieldType->getDisplayOption('icon', null),
            $fieldType->getRestrictionOption('json_nested_deny', []),
            $fieldType->getRestrictionOption('json_nested_is_leaf', false)
        );
    }
}
