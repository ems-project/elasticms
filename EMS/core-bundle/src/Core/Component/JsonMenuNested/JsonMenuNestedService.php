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
        $menu = $parentId ? $config->jsonMenuNested->getItemById($parentId) : $config->jsonMenuNested;

        if (null === $menu) {
            throw new \RuntimeException('Could not find menu');
        }

        return ['structure' => $this->getTemplate($config)->block('_itemRows', [
            'menu' => $menu
        ])];
    }

    private function getTemplate(JsonMenuNestedConfig $config): JsonMenuNestedTemplate
    {
        return $this->jsonMenuNestedTemplateFactory->create($config);
    }
}
