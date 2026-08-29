<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Mcp;

use EMS\CoreBundle\Core\ContentType\ContentTypeRoles;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Entity\DataField;
use EMS\CoreBundle\Entity\FieldType;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Form\DataField\DataFieldType;
use EMS\CoreBundle\Form\DataField\MultiplexedTabContainerFieldType;
use EMS\CoreBundle\Service\ContentTypeService;
use EMS\CoreBundle\Service\DataService;
use EMS\CoreBundle\Service\Revision\RevisionService;
use EMS\CoreBundle\Service\UserService;
use Mcp\Exception\ToolCallException;
use Mcp\Server\Builder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class ElasticmsMcpToolDataService
{
    use ElasticmsMcpToolCallTrait;

    public function __construct(
        private UserService $userService,
        private ContentTypeService $contentTypeService,
        private RevisionService $revisionService,
        private DataService $dataService,
        private FormRegistryInterface $formRegistry,
        private AuthorizationCheckerInterface $authorizationChecker,
        private LoggerInterface $logger,
        private LoggerInterface $auditLogger,
    ) {
    }

    /**
     * @return array{contentType: string, ouuid: string, revisionId: int, draft: bool, archived: bool, label: ?string, rawData: array<mixed>}
     */
    public function getDocument(string $contentType, string $ouuid): array
    {
        $toolName = \sprintf('get_%s', $contentType);

        return $this->wrapToolCall($toolName, [
            'content_type' => $contentType,
            'ouuid' => $ouuid,
        ], function () use ($contentType, $ouuid): array {
            $resolvedContentType = $this->contentTypeService->getByName($contentType);
            if (false === $resolvedContentType) {
                throw new ToolCallException(\sprintf('Content type "%s" was not found.', $contentType));
            }

            if (!$this->authorizationChecker->isGranted($resolvedContentType->role(ContentTypeRoles::VIEW))) {
                throw new ToolCallException(\sprintf('View access is not granted for content type "%s".', $contentType));
            }

            $revision = $this->revisionService->get($ouuid, $resolvedContentType->getName());
            if (!$revision instanceof Revision) {
                throw new ToolCallException(\sprintf('Content "%s" was not found for content type "%s".', $ouuid, $contentType));
            }

            return [
                'contentType' => $resolvedContentType->getName(),
                'ouuid' => $revision->giveOuuid(),
                'revisionId' => $revision->getId(),
                'draft' => $revision->isDraft(),
                'archived' => $revision->isArchived(),
                'label' => $revision->getLabel(),
                'rawData' => $this->rawDataToMcpOutput($revision),
            ];
        });
    }

    /**
     * @param array<mixed> $rawData
     *
     * @return array{contentType: string, ouuid: ?string, revisionId: int, draft: bool, archived: bool, rawData: array<mixed>}
     */
    public function saveDocument(string $contentType, array $rawData = [], ?string $ouuid = null, bool $finalize = false): array
    {
        $toolName = \sprintf('save_%s', $contentType);

        return $this->wrapToolCall($toolName, [
            'content_type' => $contentType,
            'ouuid' => $ouuid,
            'raw_data_keys' => \array_map(strval(...), \array_keys($rawData)),
        ], function () use ($rawData, $ouuid, $contentType, $finalize): array {
            $resolvedContentType = $this->contentTypeService->getByName($contentType);
            if (false === $resolvedContentType) {
                throw new ToolCallException(\sprintf('Content type "%s" was not found.', $contentType));
            }

            try {
                $resolvedContentType->validate();
            } catch (\RuntimeException $runtimeException) {
                throw new ToolCallException($runtimeException->getMessage(), 0, $runtimeException);
            }

            $revision = null;
            if (null !== $ouuid && $revision = $this->revisionService->getCurrentRevisionByOuuidAndContentType($ouuid, $contentType)) {
                $this->dataService->lockRevision($revision);
            }

            try {
                if (null === $revision) {
                    if (!$this->authorizationChecker->isGranted($resolvedContentType->role(ContentTypeRoles::CREATE))) {
                        throw new ToolCallException(\sprintf('Create access is not granted for content type "%s".', $contentType));
                    }
                    $this->dataService->hasCreateRights($resolvedContentType);
                    $revision = $this->dataService->newDocument($resolvedContentType, $ouuid, $rawData);
                } else {
                    if (!$this->authorizationChecker->isGranted($resolvedContentType->role(ContentTypeRoles::EDIT))) {
                        throw new ToolCallException(\sprintf('Edit access is not granted for content type "%s".', $contentType));
                    }
                    $revision = $this->dataService->replaceData($revision, $rawData);
                }

                if ($finalize) {
                    $revision->autoSaveToRawData();
                    $revision = $this->dataService->finalizeDraft($revision);
                }
            } catch (\Throwable $throwable) {
                throw new ToolCallException($throwable->getMessage(), 0, $throwable);
            }

            return [
                'contentType' => $resolvedContentType->getName(),
                'ouuid' => $revision->getOuuid(),
                'revisionId' => $revision->getId(),
                'draft' => $revision->isDraft(),
                'archived' => $revision->isArchived(),
                'rawData' => $this->rawDataToMcpOutput($revision),
            ];
        });
    }

    public function addGetDocumentTools(Builder $builder): void
    {
        foreach ($this->contentTypeService->getAll() as $contentType) {
            if (!$this->isViewableContentType($contentType)) {
                continue;
            }

            $contentTypeName = $contentType->getName();

            $builder->addTool(
                handler: fn (string $ouuid): array => $this->getDocument($contentTypeName, $ouuid),
                name: \sprintf('get_%s', $contentTypeName),
                description: \sprintf('Read the current elasticMS revision for a %s document from the %s environment. You must already know the document OUUID; use the search tool first when you need to discover documents. Recoverable errors include missing OUUIDs, archived or deleted revisions and permission failures.', $contentTypeName, $contentType->giveEnvironment()->getName()),
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'ouuid' => [
                            'type' => 'string',
                            'description' => 'elasticMS object UUID of the document revision to read.',
                        ],
                    ],
                    'required' => ['ouuid'],
                    'additionalProperties' => false,
                ],
                outputSchema: $this->buildGetDocumentOutputSchema($contentType),
            );
        }
    }

    public function addSaveDocumentTools(Builder $builder): void
    {
        foreach ($this->contentTypeService->getAll() as $contentType) {
            if (!$this->isSavableContentType($contentType)) {
                continue;
            }

            $contentTypeName = $contentType->getName();

            $builder->addTool(
                handler: fn (array $rawData = [], ?string $ouuid = null, bool $finalize = false): array => $this->saveDocument($contentTypeName, $rawData, $ouuid, $finalize),
                name: \sprintf('save_%s', $contentTypeName),
                description: \sprintf('Create or update a `%s` in the `%s` environment. Provide rawData according to the generated schema for this content type. Omit ouuid to let elasticMS generate one; if an explicit ouuid already exists, creation may fail. By default the new revision remains a draft in progress. Set finalize=true only when the rawData is complete and should be finalized directly in the content type default environment, which triggers the normal elasticMS validation/finalization flow. Recoverable errors include invalid rawData, duplicate OUUIDs, validation failures and permission failures.', $contentTypeName, $contentType->giveEnvironment()->getName()),
                inputSchema: $this->buildSaveDocumentInputSchema($contentType),
                outputSchema: $this->buildSaveDocumentOutputSchema($contentType),
            );
        }
    }

    private function isViewableContentType(ContentType $contentType): bool
    {
        return $contentType->giveEnvironment()->getManaged()
            && $contentType->isActive()
            && $this->authorizationChecker->isGranted($contentType->role(ContentTypeRoles::VIEW));
    }

    private function isSavableContentType(ContentType $contentType): bool
    {
        return $contentType->giveEnvironment()->getManaged()
            && $contentType->isActive()
            && ($this->authorizationChecker->isGranted($contentType->role(ContentTypeRoles::CREATE))
            || $this->authorizationChecker->isGranted($contentType->role(ContentTypeRoles::EDIT)));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildGetDocumentOutputSchema(ContentType $contentType): array
    {
        $rawDataSchema = $this->buildRawDataSchema($contentType->getFieldType(), filterEditableFields: false, includeRequired: false);
        $rawDataSchema['additionalProperties'] = true;

        return [
            'type' => 'object',
            'properties' => [
                'contentType' => ['type' => 'string'],
                'ouuid' => ['type' => 'string'],
                'revisionId' => ['type' => 'integer'],
                'draft' => ['type' => 'boolean'],
                'archived' => ['type' => 'boolean'],
                'label' => [
                    'type' => [
                        'anyOf' => [[
                            'type' => 'string',
                        ], [
                            'type' => 'null',
                        ]],
                    ],
                ],
                'rawData' => $rawDataSchema,
            ],
            'required' => ['contentType', 'ouuid', 'revisionId', 'draft', 'archived', 'rawData'],
            'additionalProperties' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSaveDocumentInputSchema(ContentType $contentType): array
    {
        $rawDataSchema = $this->buildRawDataSchema($contentType->getFieldType(), filterEditableFields: true, includeRequired: true);
        $rawDataSchema['additionalProperties'] = true;

        return ElasticmsMcpJsonSchema::normalize([
            'type' => 'object',
            'properties' => [
                'rawData' => $rawDataSchema,
                'ouuid' => [
                    'type' => 'string',
                    'description' => 'Optional elasticMS object UUID. When omitted, elasticMS will generate one. If provided, it must be unique for all content types.',
                ],
                'finalize' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'When true, finalize the new revision directly in the content type default environment and run the normal elasticMS validation/finalization flow. When false or omitted, keep the document as a draft in progress.',
                ],
            ],
            'required' => ['rawData'],
            'additionalProperties' => false,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSaveDocumentOutputSchema(ContentType $contentType): array
    {
        $rawDataSchema = $this->buildRawDataSchema($contentType->getFieldType(), filterEditableFields: false, includeRequired: false);
        $rawDataSchema['additionalProperties'] = true;

        return [
            'type' => 'object',
            'properties' => [
                'contentType' => ['type' => 'string'],
                'ouuid' => ['type' => 'string'],
                'revisionId' => ['type' => 'integer'],
                'draft' => ['type' => 'boolean'],
                'archived' => ['type' => 'boolean'],
                'rawData' => $rawDataSchema,
            ],
            'required' => ['contentType', 'ouuid', 'revisionId', 'draft', 'archived', 'rawData'],
            'additionalProperties' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRawDataSchema(FieldType $rootFieldType, bool $filterEditableFields = true, bool $includeRequired = true): array
    {
        return $this->buildObjectSchemaFromChildren($rootFieldType->getValidChildren(), $filterEditableFields, $includeRequired);
    }

    /**
     * @param FieldType[] $fieldTypes
     *
     * @return array<string, mixed>
     */
    private function buildObjectSchemaFromChildren(array $fieldTypes, bool $filterEditableFields = true, bool $includeRequired = true): array
    {
        $properties = [];
        $required = [];

        foreach ($fieldTypes as $fieldType) {
            $this->appendFieldSchema($fieldType, $properties, $required, $filterEditableFields, $includeRequired);
        }

        $schema = [
            'type' => 'object',
            'properties' => [] === $properties ? new \stdClass() : $properties,
            'additionalProperties' => false,
        ];

        if ($includeRequired && [] !== $required) {
            $schema['required'] = \array_values(\array_unique($required));
        }

        return ElasticmsMcpJsonSchema::normalize($schema);
    }

    /**
     * @param array<mixed>       $properties
     * @param array<int, string> $required
     */
    private function appendFieldSchema(FieldType $fieldType, array &$properties, array &$required, bool $filterEditableFields = true, bool $includeRequired = true): void
    {
        if ($fieldType->isDeleted() || ($filterEditableFields && !$this->authorizationChecker->isGranted($fieldType->getMinimumRole()))) {
            return;
        }

        $fieldTypeClass = $fieldType->getType();

        if ($fieldTypeClass::isVirtual($fieldType->getOptions())) {
            if (MultiplexedTabContainerFieldType::class === $fieldTypeClass) {
                $schema = $this->buildFieldSchema($fieldType, $filterEditableFields, $includeRequired);
                foreach ($schema['properties'] ?? [] as $propertyName => $propertySchema) {
                    $properties[$propertyName] = $propertySchema;
                }

                return;
            }

            foreach ($fieldType->getValidChildren() as $childFieldType) {
                $this->appendFieldSchema($childFieldType, $properties, $required, $filterEditableFields, $includeRequired);
            }

            return;
        }

        $properties[$fieldType->getName()] = $this->buildFieldSchema($fieldType, $filterEditableFields, $includeRequired);

        if ($includeRequired && (bool) $fieldType->getRestrictionOption('mandatory', false)) {
            $required[] = $fieldType->getName();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFieldSchema(FieldType $fieldType, bool $filterEditableFields = true, bool $includeRequired = true): array
    {
        $schema = $this->getDataFieldType($fieldType)->generateJsonSchema($fieldType, fn (array $fieldTypes): array => $this->buildObjectSchemaFromChildren($fieldTypes, $filterEditableFields, $includeRequired));

        $schema['title'] ??= (string) $fieldType->getDisplayOption('label', $fieldType->getName());
        $description = $fieldType->getExtraOption('description', $fieldType->getDescription());

        if (\is_string($description) && '' !== \trim($description)) {
            $schema['description'] = $description;
        }

        return ElasticmsMcpJsonSchema::normalize($schema);
    }

    private function getDataFieldType(FieldType $fieldType): DataFieldType
    {
        $innerType = $this->formRegistry->getType($fieldType->getType())->getInnerType();

        if (!$innerType instanceof DataFieldType) {
            throw new \RuntimeException(\sprintf('Unexpected form type "%s" for field "%s".', $fieldType->getType(), $fieldType->getName()));
        }

        return $innerType;
    }

    /**
     * @return mixed[]
     */
    private function rawDataToMcpOutput(Revision $revision): array
    {
        $rawData = $revision->getRawData();
        if (!$revision->getDataField() instanceof DataField) {
            $this->dataService->loadDataStructure($revision, true);
        }

        $dataField = $revision->getDataField();
        if (!$dataField instanceof DataField) {
            return $rawData;
        }

        return $this->buildMcpRawDataFromDataFields($dataField->getChildren(), $rawData);
    }

    /**
     * @param iterable<int, DataField> $dataFields
     * @param array<string, mixed>     $rawData
     *
     * @return array<string, mixed>
     */
    private function buildMcpRawDataFromDataFields(iterable $dataFields, array $rawData): array
    {
        $output = $rawData;

        foreach ($dataFields as $dataField) {
            $fieldType = $dataField->getFieldType();
            if (!$fieldType instanceof FieldType) {
                continue;
            }

            $this->appendMcpFieldValue($fieldType, $rawData, $output);
        }

        return $output;
    }

    /**
     * @param iterable<int, FieldType> $fieldTypes
     * @param array<string, mixed>     $rawData
     *
     * @return array<string, mixed>
     */
    private function buildMcpRawDataFromFieldTypes(iterable $fieldTypes, array $rawData): array
    {
        $output = $rawData;

        foreach ($fieldTypes as $fieldType) {
            $this->appendMcpFieldValue($fieldType, $rawData, $output);
        }

        return $output;
    }

    /**
     * @param array<string, mixed> $rawData
     * @param array<string, mixed> $output
     */
    private function appendMcpFieldValue(FieldType $fieldType, array $rawData, array &$output): void
    {
        if ($fieldType->isDeleted()) {
            return;
        }

        $fieldTypeClass = $fieldType->getType();

        if ($fieldTypeClass::isVirtual($fieldType->getOptions())) {
            if (MultiplexedTabContainerFieldType::class === $fieldTypeClass) {
                $value = $this->buildMcpValueForFieldType($fieldType, $rawData);
                if (\is_array($value)) {
                    foreach ($value as $propertyName => $propertyValue) {
                        $output[$propertyName] = $propertyValue;
                    }
                }

                return;
            }

            foreach ($fieldType->getValidChildren() as $childFieldType) {
                $this->appendMcpFieldValue($childFieldType, $rawData, $output);
            }

            return;
        }

        if (!\array_key_exists($fieldType->getName(), $rawData)) {
            return;
        }

        $output[$fieldType->getName()] = $this->buildMcpValueForFieldType($fieldType, $rawData[$fieldType->getName()]);
    }

    private function buildMcpValueForFieldType(FieldType $fieldType, mixed $rawData): mixed
    {
        $fieldTypeClass = $fieldType->getType();

        if ($fieldTypeClass::isVirtual($fieldType->getOptions())) {
            if (!$fieldTypeClass::isContainer()) {
                return $this->getDataFieldType($fieldType)->buildMcpRawDataValue(
                    $fieldType,
                    $rawData,
                    fn (FieldType $childFieldType, mixed $childRawData): mixed => $this->buildMcpValueForFieldType($childFieldType, $childRawData),
                );
            }

            if (!\is_array($rawData)) {
                return [];
            }

            $jsonNames = $fieldTypeClass::getJsonNames($fieldType);
            if ([] === $jsonNames) {
                return $this->buildMcpRawDataFromFieldTypes($fieldType->getValidChildren(), $rawData);
            }

            $output = [];
            foreach ($jsonNames as $name) {
                if (!isset($rawData[$name]) || !\is_array($rawData[$name])) {
                    continue;
                }

                $output[$name] = $this->buildMcpRawDataFromFieldTypes($fieldType->getValidChildren(), $rawData[$name]);
            }

            return $output;
        }

        if ($fieldTypeClass::isCollection()) {
            if (!\is_array($rawData)) {
                return $rawData;
            }

            $items = [];
            foreach ($rawData as $item) {
                $items[] = \is_array($item)
                    ? $this->buildMcpRawDataFromFieldTypes($fieldType->getValidChildren(), $item)
                    : $item;
            }

            return $items;
        }

        if ($fieldTypeClass::isContainer() && \is_array($rawData)) {
            return $this->buildMcpRawDataFromFieldTypes($fieldType->getValidChildren(), $rawData);
        }

        return $this->getDataFieldType($fieldType)->buildMcpRawDataValue(
            $fieldType,
            $rawData,
            fn (FieldType $childFieldType, mixed $childRawData): mixed => $this->buildMcpValueForFieldType($childFieldType, $childRawData),
        );
    }
}
