<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Action;

use EMS\CoreBundle\Core\ContentType\FieldType\FieldTypeService;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Service\Revision\RevisionService;

class ActionRevisionService
{
    public function __construct(
        private readonly RevisionService $revisionService,
        private readonly FieldTypeService $fieldTypeService,
    ) {
    }

    /**
     * @return array{ 'output': string[], 'revisionId': int }
     */
    public function create(int $revisionId, int $fieldId): array
    {
        $revision = $this->getDraftRevision($revisionId);
        $config = $this->getConfig($fieldId);

        return ['output' => $config->outputNames(), 'revisionId' => $revision->getId()];
    }

    private function getConfig(int $fieldId): ActionRevisionConfig
    {
        $fieldType = $this->fieldTypeService->getById($fieldId);
        $fieldTree = $this->fieldTypeService->getTree($fieldType->giveContentType());

        return ActionRevisionConfig::fromFieldType($fieldType, $fieldTree);
    }

    private function getDraftRevision(int $revisionId): Revision
    {
        $revision = $this->revisionService->getByRevisionId($revisionId);
        if (!$revision->isDraft()) {
            throw new \RuntimeException('Revision is not in draft');
        }

        return $revision;
    }
}
