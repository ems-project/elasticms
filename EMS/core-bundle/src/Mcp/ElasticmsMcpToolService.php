<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Mcp;

use EMS\CoreBundle\Core\ContentType\ContentTypeRoles;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Entity\FieldType;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Form\DataField\DataFieldType;
use EMS\CoreBundle\Service\ContentTypeService;
use EMS\CoreBundle\Service\DataService;
use EMS\CoreBundle\Service\Revision\RevisionService;
use EMS\CoreBundle\Service\UserService;
use Mcp\Exception\ToolCallException;
use Mcp\Server\Builder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class ElasticmsMcpToolService
{
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
     * @return array{user: array<mixed>}
     */
    public function getCurrentUser(): array
    {
        return $this->wrapToolCall('get_current_user', [], fn (): array => [
            'user' => $this->userService->getCurrentUser()->toArray(),
        ]);
    }

    /**
     * @return array{contentType: string, ouuid: string, revisionId: int, draft: bool, archived: bool, label: ?string, rawData: array<mixed>}
     */
    public function getDocument(string $contentType, string $ouuid): array
    {
        $toolName = \sprintf('get_document_%s', $contentType);

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
                'rawData' => $revision->getRawData(),
            ];
        });
    }

    /**
     * @param array<mixed> $rawData
     *
     * @return array{contentType: string, ouuid: ?string, revisionId: int, draft: true, rawData: array<mixed>}
     */
    public function createDocument(string $contentType, array $rawData = [], ?string $ouuid = null): array
    {
        $toolName = \sprintf('create_document_%s', $contentType);

        return $this->wrapToolCall($toolName, [
            'content_type' => $contentType,
            'ouuid' => $ouuid,
            'raw_data_keys' => \array_map('strval', \array_keys($rawData)),
        ], function () use ($rawData, $ouuid, $contentType): array {
            $resolvedContentType = $this->contentTypeService->getByName($contentType);
            if (false === $resolvedContentType) {
                throw new ToolCallException(\sprintf('Content type "%s" was not found.', $contentType));
            }

            try {
                $resolvedContentType->validate();
            } catch (\RuntimeException $exception) {
                throw new ToolCallException($exception->getMessage(), 0, $exception);
            }

            if (!$this->authorizationChecker->isGranted($resolvedContentType->role(ContentTypeRoles::CREATE))) {
                throw new ToolCallException(\sprintf('Create access is not granted for content type "%s".', $contentType));
            }

            try {
                $this->dataService->hasCreateRights($resolvedContentType);
                $revision = $this->dataService->newDocument($resolvedContentType, $ouuid, $rawData);
            } catch (\Throwable $exception) {
                throw new ToolCallException($exception->getMessage(), 0, $exception);
            }

            return [
                'contentType' => $resolvedContentType->getName(),
                'ouuid' => $revision->getOuuid(),
                'revisionId' => $revision->getId(),
                'draft' => true,
                'rawData' => $revision->getRawData(),
            ];
        });
    }

    /**
     * @template TResult
     *
     * @param array<string, mixed> $context
     * @param \Closure(): TResult  $callable
     *
     * @return TResult
     */
    private function wrapToolCall(string $toolName, array $context, \Closure $callable): mixed
    {
        $logContext = [
            'tool' => $toolName,
            'username' => $this->userService->getCurrentUser()->getUsername(),
            ...$context,
        ];

        $this->logger->info('mcp.tool.called', $logContext);
        $this->auditLogger->info('mcp.tool.called', $logContext);

        try {
            $result = $callable();

            $this->logger->info('mcp.tool.succeeded', $logContext);
            $this->auditLogger->info('mcp.tool.succeeded', $logContext);

            return $result;
        } catch (\Throwable $exception) {
            $errorContext = [...$logContext, 'error_message' => $exception->getMessage()];

            $this->logger->error('mcp.tool.failed', [...$errorContext, 'exception' => $exception]);
            $this->auditLogger->error('mcp.tool.failed', $errorContext);

            if ($exception instanceof ToolCallException) {
                throw $exception;
            }

            throw new ToolCallException($exception->getMessage(), 0, $exception);
        }
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
                name: \sprintf('get_document_%s', $contentTypeName),
                description: \sprintf('Read the current content revision for the %s content type indexed in the %s environment.', $contentTypeName, $contentType->giveEnvironment()->getName()),
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'ouuid' => ['type' => 'string'],
                    ],
                    'required' => ['ouuid'],
                    'additionalProperties' => false,
                ],
                outputSchema: $this->buildGetDocumentOutputSchema($contentType),
            );
        }
    }

    public function addCreateDocumentTools(Builder $builder): void
    {
        foreach ($this->contentTypeService->getAll() as $contentType) {
            if (!$this->isCreatableContentType($contentType)) {
                continue;
            }

            $contentTypeName = $contentType->getName();

            $builder->addTool(
                handler: fn (array $rawData = [], ?string $ouuid = null): array => $this->createDocument($contentTypeName, $rawData, $ouuid),
                name: \sprintf('create_document_%s', $contentTypeName),
                description: \sprintf('Create a new document in the %s content type indexed in the %s environment.', $contentTypeName, $contentType->giveEnvironment()->getName()),
                inputSchema: $this->buildCreateDocumentInputSchema($contentType),
            );
        }
    }

    private function isViewableContentType(ContentType $contentType): bool
    {
        return $contentType->giveEnvironment()->getManaged()
            && $contentType->isActive()
            && $this->authorizationChecker->isGranted($contentType->role(ContentTypeRoles::VIEW));
    }

    private function isCreatableContentType(ContentType $contentType): bool
    {
        return $contentType->giveEnvironment()->getManaged()
            && $contentType->isActive()
            && $this->authorizationChecker->isGranted($contentType->role(ContentTypeRoles::CREATE));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildGetDocumentOutputSchema(ContentType $contentType): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'contentType' => ['type' => 'string'],
                'ouuid' => ['type' => 'string'],
                'revisionId' => ['type' => 'integer'],
                'draft' => ['type' => 'boolean'],
                'archived' => ['type' => 'boolean'],
                'label' => [
                    'type' => ['string', 'null'],
                ],
                'rawData' => $this->buildRawDataSchema($contentType->getFieldType(), filterEditableFields: false, includeRequired: false),
            ],
            'required' => ['contentType', 'ouuid', 'revisionId', 'draft', 'archived', 'rawData'],
            'additionalProperties' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCreateDocumentInputSchema(ContentType $contentType): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'rawData' => $this->buildRawDataSchema($contentType->getFieldType()),
                'ouuid' => [
                    'type' => 'string',
                    'description' => 'Optional OUUID. When omitted, ElasticMS will generate one.',
                ],
            ],
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
            'properties' => $properties,
            'additionalProperties' => false,
        ];

        if ($includeRequired && [] !== $required) {
            $schema['required'] = \array_values(\array_unique($required));
        }

        return $schema;
    }

    /**
     * @param array<string, mixed> $properties
     * @param array<int, string>   $required
     */
    private function appendFieldSchema(FieldType $fieldType, array &$properties, array &$required, bool $filterEditableFields = true, bool $includeRequired = true): void
    {
        if ($fieldType->isDeleted() || ($filterEditableFields && !$this->authorizationChecker->isGranted($fieldType->getMinimumRole()))) {
            return;
        }

        $fieldTypeClass = $fieldType->getType();

        if ($fieldTypeClass::isVirtual($fieldType->getOptions())) {
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

        if (\is_string($description = $fieldType->getDescription()) && '' !== $description) {
            $schema['description'] = $description;
        }

        return $schema;
    }

    private function getDataFieldType(FieldType $fieldType): DataFieldType
    {
        $innerType = $this->formRegistry->getType($fieldType->getType())->getInnerType();

        if (!$innerType instanceof DataFieldType) {
            throw new \RuntimeException(\sprintf('Unexpected form type "%s" for field "%s".', $fieldType->getType(), $fieldType->getName()));
        }

        return $innerType;
    }
}
