<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\JsonMenuNested;

use EMS\CoreBundle\Core\Component\JsonMenuNested\Config\JsonMenuNestedConfig;
use EMS\CoreBundle\Core\Component\JsonMenuNested\Template\JsonMenuNestedTemplate;
use EMS\CoreBundle\Core\Component\JsonMenuNested\Template\JsonMenuNestedTemplateFactory;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Service\Revision\RevisionService;
use EMS\CoreBundle\Service\UserService;
use EMS\Helpers\Standard\Json;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class JsonMenuNestedService
{
    public function __construct(
        private readonly JsonMenuNestedTemplateFactory $jsonMenuNestedTemplateFactory,
        private readonly RevisionService $revisionService,
        private readonly UserService $userService
    ) {
    }

    /**
     * @param string[] $load
     */
    public function getStructure(JsonMenuNestedConfig $config, ?string $parentId = null, array $load = []): string
    {
        $menu = $parentId ? $config->jsonMenuNested->getItemById($parentId) : $config->jsonMenuNested;

        if (null === $menu) {
            throw new \RuntimeException('Could not find menu');
        }

        return $this->getTemplate($config)->block('_itemNodes', [
            'menu' => $menu,
            'load' => $load,
        ]);
    }

    /**
     * @return array{hash: string}
     */
    public function itemDelete(JsonMenuNestedConfig $config, string $itemId): array
    {
        $item = $config->jsonMenuNested->getItemById($itemId);
        $parent = $item?->getParent();

        if (null === $item || null === $parent) {
            throw new \RuntimeException('Could not item');
        }

        $parent->removeChild($item);

        return [
            'hash' => $this->saveStructure($config)->getHash(),
        ];
    }

    private function getTemplate(JsonMenuNestedConfig $config): JsonMenuNestedTemplate
    {
        return $this->jsonMenuNestedTemplateFactory->create($config);
    }

    private function saveStructure(JsonMenuNestedConfig $config): Revision
    {
        $path = $config->nodes->path;
        $structure = Json::encode($config->jsonMenuNested->toArrayStructure());
        $username = $this->userService->getCurrentUser()->getUsername();

        $rawData = [];
        (new PropertyAccessor())->setValue($rawData, $path, $structure);

        return $this->revisionService->updateRawData($config->revision, $rawData, $username);
    }
}
