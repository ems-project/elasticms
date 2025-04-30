<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Action;

use EMS\CoreBundle\Core\ContentType\FieldType\FieldTypeService;
use EMS\CoreBundle\Core\Revision\RawDataTransformer;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Service\Revision\RevisionService;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ActionRevisionService
{
    private readonly PropertyAccessor $propertyAccessor;

    public function __construct(
        private readonly ActionService $actionService,
        private readonly RevisionService $revisionService,
        private readonly FieldTypeService $fieldTypeService
    ) {
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * @return array{ 'outputFields': string[], 'action': string }
     */
    public function handle(int $revisionId, int $fieldId): array
    {
        $revision = $this->getDraftRevision($revisionId);
        $config = $this->getConfig($fieldId);

        $inputData = $this->getInputData($revision, $config);
        $outputObject = $this->getOutputObject($config);

        $action = $this->actionService->requestFromRevision($revision, [
            'input' => $inputData,
            'output' => $outputObject,
        ]);

        return ['outputFields' => $config->getOutputFields(), 'action' => $action->getId()->toString()];
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

    /**
     * @return array<string, mixed>
     */
    private function getInputData(Revision $revision, ActionRevisionConfig $config): array
    {
        $inputData = [];

        $data = $revision->getAutoSave() ?? $revision->getRawData();
        $rawData = RawDataTransformer::transform($revision->giveContentType()->getFieldType(), $data);

        foreach ($config->getInputPaths() as $inputPath) {
            if (!$this->propertyAccessor->isReadable($rawData, $inputPath)) {
                continue;
            }

            $value = $this->propertyAccessor->getValue($rawData, $inputPath);
            $this->propertyAccessor->setValue($inputData, $inputPath, $value);
        }

        return $inputData;
    }

    /**
     * @return array<string, mixed>
     */
    private function getOutputObject(ActionRevisionConfig $config): array
    {
        $outputObject = [];

        foreach ($config->getOutputPaths() as $outputPath) {
            $this->propertyAccessor->setValue($outputObject, $outputPath, null);
        }

        return $outputObject;
    }
}
