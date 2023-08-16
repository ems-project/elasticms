<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\JsonMenuNested;

use EMS\CommonBundle\Json\JsonMenuNested;
use EMS\CoreBundle\Core\Component\JsonMenuNested\Config\JsonMenuNestedConfig;
use EMS\CoreBundle\Core\Component\JsonMenuNested\Template\JsonMenuNestedTemplate;
use EMS\CoreBundle\Core\Component\JsonMenuNested\Template\JsonMenuNestedTemplateFactory;

class JsonMenuNestedService
{
    public function __construct(
        private readonly JsonMenuNestedTemplateFactory $jsonMenuNestedTemplateFactory
    ) {
    }

    /**
     * @return array{rows: string[]}
     */
    public function getStructure(JsonMenuNestedConfig $config, ?string $parentId = null): array
    {
        $parent = $parentId ? $config->jsonMenuNested->getItemById($parentId) : $config->jsonMenuNested;

        if (null === $parent) {
            throw new \RuntimeException('Could not find parent');
        }

        $template = $this->getTemplate($config);
        $children = $parent->getChildren();
        $rows = \array_map(static fn (JsonMenuNested $item) => $template->block('_itemRow', [
            'item' => $item,
            'node' => $config->nodes->get($item),
        ]), $children);

        return ['rows' => $rows];
    }

    private function getTemplate(JsonMenuNestedConfig $config): JsonMenuNestedTemplate
    {
        return $this->jsonMenuNestedTemplateFactory->create($config);
    }
}
