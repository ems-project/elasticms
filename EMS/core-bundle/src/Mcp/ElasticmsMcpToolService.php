<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Mcp;

use EMS\CoreBundle\Core\ContentType\ContentTypeRoles;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Entity\FieldType;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Entity\UploadedAsset;
use EMS\CoreBundle\Form\DataField\DataFieldType;
use EMS\CoreBundle\Service\ContentTypeService;
use EMS\CoreBundle\Service\DataService;
use EMS\CoreBundle\Service\FileService;
use EMS\CoreBundle\Service\Revision\RevisionService;
use EMS\CoreBundle\Service\UserService;
use EMS\Helpers\File\File;
use Mcp\Exception\ToolCallException;
use Mcp\Server\Builder;
use Psr\Http\Message\StreamInterface;
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
        private FileService $fileService,
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
     * @return array{hash:string, name:string, type:string, size:int, algo:string, available:bool, uploaded:int, status:?string, user:string, chunkSize:int}
     */
    public function initAssetUpload(string $hash, int $size, string $name, string $type, ?string $algo = null): array
    {
        $toolName = 'init_asset_upload';
        $resolvedAlgo = $algo ?? $this->fileService->getAlgo();

        return $this->wrapToolCall($toolName, [
            'hash' => $hash,
            'size' => $size,
            'name' => $name,
            'type' => $type,
            'algo' => $resolvedAlgo,
        ], function () use ($hash, $size, $name, $type, $resolvedAlgo): array {
            if ('' === $hash || '' === $name || '' === $type || $size < 0) {
                throw new ToolCallException('Invalid asset upload initialization arguments.');
            }

            $uploadedAsset = $this->fileService->initUploadFile($hash, $size, $name, $type, $this->userService->getCurrentUser()->getUsername(), $resolvedAlgo);

            return $this->buildAssetUploadState($uploadedAsset);
        });
    }

    /**
     * @return array{hash:string, name:string, type:string, size:int, algo:string, available:bool, uploaded:int, status:?string, user:string, chunkSize:int}
     */
    public function uploadAssetChunk(string $hash, string $chunkBase64): array
    {
        $toolName = 'upload_asset_chunk';

        return $this->wrapToolCall($toolName, [
            'hash' => $hash,
        ], function () use ($hash, $chunkBase64): array {
            $chunk = \base64_decode($chunkBase64, true);
            if (!\is_string($chunk)) {
                throw new ToolCallException('The chunkBase64 argument must be a valid base64 string.');
            }
            if ('' === $hash) {
                throw new ToolCallException('The hash argument must not be empty.');
            }
            if (\strlen($chunk) > File::DEFAULT_CHUNK_SIZE) {
                throw new ToolCallException(\sprintf('Chunk size exceeds %d bytes.', File::DEFAULT_CHUNK_SIZE));
            }

            $uploadedAsset = $this->fileService->addChunk($hash, $chunk, $this->userService->getCurrentUser()->getUsername());

            return $this->buildAssetUploadState($uploadedAsset);
        });
    }

    /**
     * @return array{hash:string, name:string, type:string, size:int, algo:string, offset:int, requestedLength:int, bytesRead:int, nextOffset:int, eof:bool, chunkBase64:string}
     */
    public function downloadAssetChunk(string $hash, int $offset = 0, ?int $length = null): array
    {
        $toolName = 'download_asset_chunk';
        $resolvedLength = $length ?? File::DEFAULT_CHUNK_SIZE;

        return $this->wrapToolCall($toolName, [
            'hash' => $hash,
            'offset' => $offset,
            'length' => $resolvedLength,
        ], function () use ($hash, $offset, $resolvedLength): array {
            if ('' === $hash) {
                throw new ToolCallException('The hash argument must not be empty.');
            }
            if ($offset < 0) {
                throw new ToolCallException('The offset argument must be greater than or equal to 0.');
            }
            if ($resolvedLength < 1 || $resolvedLength > File::DEFAULT_CHUNK_SIZE) {
                throw new ToolCallException(\sprintf('The length argument must be between 1 and %d bytes.', File::DEFAULT_CHUNK_SIZE));
            }

            $stream = $this->fileService->getResource($hash);
            if (!$stream instanceof StreamInterface) {
                throw new ToolCallException(\sprintf('Asset "%s" was not found.', $hash));
            }

            $fileObject = $this->fileService->getFileObject($hash);
            $this->seekStream($stream, $offset);
            $chunk = $stream->read($resolvedLength);
            $bytesRead = \strlen($chunk);
            $nextOffset = $offset + $bytesRead;
            $size = (int) $fileObject['_size'];

            return [
                'hash' => $hash,
                'name' => (string) $fileObject['_name'],
                'type' => (string) $fileObject['_type'],
                'size' => $size,
                'algo' => (string) $fileObject['_algo'],
                'offset' => $offset,
                'requestedLength' => $resolvedLength,
                'bytesRead' => $bytesRead,
                'nextOffset' => $nextOffset,
                'eof' => $nextOffset >= $size,
                'chunkBase64' => \base64_encode($chunk),
            ];
        });
    }

    /**
     * @return array{hash:string, name:string, type:string, size:int, algo:string, fileObject:array{sha1:string, _hash:string, filesize:int, _size:int, filename:string, _name:string, mimetype:string, _type:string, _algo:string}}
     */
    public function getAssetInfo(string $hash): array
    {
        $toolName = 'get_asset_info';

        return $this->wrapToolCall($toolName, [
            'hash' => $hash,
        ], function () use ($hash): array {
            if ('' === $hash) {
                throw new ToolCallException('The hash argument must not be empty.');
            }

            $fileObject = $this->fileService->getFileObject($hash);

            return [
                'hash' => $hash,
                'name' => (string) $fileObject['_name'],
                'type' => (string) $fileObject['_type'],
                'size' => (int) $fileObject['_size'],
                'algo' => (string) $fileObject['_algo'],
                'fileObject' => $fileObject,
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

    public function addAssetTools(Builder $builder): void
    {
        $builder
            ->addTool(
                handler: $this->initAssetUpload(...),
                name: 'init_asset_upload',
                description: \sprintf('Initialize or resume a chunked asset upload. Chunks must not exceed %d bytes and the hash must use the current storage algorithm.', File::DEFAULT_CHUNK_SIZE),
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'hash' => ['type' => 'string'],
                        'size' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                        'type' => ['type' => 'string'],
                        'algo' => ['type' => 'string'],
                    ],
                    'required' => ['hash', 'size', 'name', 'type'],
                    'additionalProperties' => false,
                ],
                outputSchema: $this->buildAssetUploadStateSchema(),
            )
            ->addTool(
                handler: $this->uploadAssetChunk(...),
                name: 'upload_asset_chunk',
                description: \sprintf('Upload one asset chunk encoded as base64. The decoded chunk size must not exceed %d bytes.', File::DEFAULT_CHUNK_SIZE),
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'hash' => ['type' => 'string'],
                        'chunkBase64' => ['type' => 'string'],
                    ],
                    'required' => ['hash', 'chunkBase64'],
                    'additionalProperties' => false,
                ],
                outputSchema: $this->buildAssetUploadStateSchema(),
            )
            ->addTool(
                handler: $this->downloadAssetChunk(...),
                name: 'download_asset_chunk',
                description: \sprintf('Download one asset chunk encoded as base64. The requested chunk length defaults to and must not exceed %d bytes.', File::DEFAULT_CHUNK_SIZE),
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'hash' => ['type' => 'string'],
                        'offset' => ['type' => 'integer'],
                        'length' => ['type' => 'integer'],
                    ],
                    'required' => ['hash'],
                    'additionalProperties' => false,
                ],
                outputSchema: $this->buildAssetDownloadChunkSchema(),
            )
            ->addTool(
                handler: $this->getAssetInfo(...),
                name: 'get_asset_info',
                description: 'Return the metadata and file object of an uploaded asset.',
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'hash' => ['type' => 'string'],
                    ],
                    'required' => ['hash'],
                    'additionalProperties' => false,
                ],
                outputSchema: $this->buildAssetInfoSchema(),
            );
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
     * @return array{hash:string, name:string, type:string, size:int, algo:string, available:bool, uploaded:int, status:?string, user:string, chunkSize:int}
     */
    private function buildAssetUploadState(object $uploadedAsset): array
    {
        if (!$uploadedAsset instanceof UploadedAsset) {
            throw new \RuntimeException('Unexpected uploaded asset type.');
        }

        return [
            'hash' => $uploadedAsset->getSha1(),
            'name' => $uploadedAsset->getName(),
            'type' => $uploadedAsset->getType(),
            'size' => $uploadedAsset->getSize(),
            'algo' => $uploadedAsset->getHashAlgo(),
            'available' => $uploadedAsset->getAvailable(),
            'uploaded' => $uploadedAsset->getUploaded(),
            'status' => $uploadedAsset->getStatus(),
            'user' => $uploadedAsset->getUser(),
            'chunkSize' => File::DEFAULT_CHUNK_SIZE,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAssetUploadStateSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'hash' => ['type' => 'string'],
                'name' => ['type' => 'string'],
                'type' => ['type' => 'string'],
                'size' => ['type' => 'integer'],
                'algo' => ['type' => 'string'],
                'available' => ['type' => 'boolean'],
                'uploaded' => ['type' => 'integer'],
                'status' => ['type' => ['string', 'null']],
                'user' => ['type' => 'string'],
                'chunkSize' => ['type' => 'integer'],
            ],
            'required' => ['hash', 'name', 'type', 'size', 'algo', 'available', 'uploaded', 'status', 'user', 'chunkSize'],
            'additionalProperties' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAssetDownloadChunkSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'hash' => ['type' => 'string'],
                'name' => ['type' => 'string'],
                'type' => ['type' => 'string'],
                'size' => ['type' => 'integer'],
                'algo' => ['type' => 'string'],
                'offset' => ['type' => 'integer'],
                'requestedLength' => ['type' => 'integer'],
                'bytesRead' => ['type' => 'integer'],
                'nextOffset' => ['type' => 'integer'],
                'eof' => ['type' => 'boolean'],
                'chunkBase64' => ['type' => 'string'],
            ],
            'required' => ['hash', 'name', 'type', 'size', 'algo', 'offset', 'requestedLength', 'bytesRead', 'nextOffset', 'eof', 'chunkBase64'],
            'additionalProperties' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAssetInfoSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'hash' => ['type' => 'string'],
                'name' => ['type' => 'string'],
                'type' => ['type' => 'string'],
                'size' => ['type' => 'integer'],
                'algo' => ['type' => 'string'],
                'fileObject' => [
                    'type' => 'object',
                    'properties' => [
                        'sha1' => ['type' => 'string'],
                        '_hash' => ['type' => 'string'],
                        'filesize' => ['type' => 'integer'],
                        '_size' => ['type' => 'integer'],
                        'filename' => ['type' => 'string'],
                        '_name' => ['type' => 'string'],
                        'mimetype' => ['type' => 'string'],
                        '_type' => ['type' => 'string'],
                        '_algo' => ['type' => 'string'],
                    ],
                    'required' => ['sha1', '_hash', 'filesize', '_size', 'filename', '_name', 'mimetype', '_type', '_algo'],
                    'additionalProperties' => false,
                ],
            ],
            'required' => ['hash', 'name', 'type', 'size', 'algo', 'fileObject'],
            'additionalProperties' => false,
        ];
    }

    private function seekStream(StreamInterface $stream, int $offset): void
    {
        if (0 === $offset) {
            return;
        }

        if ($stream->isSeekable()) {
            $stream->seek($offset);

            return;
        }

        $remaining = $offset;
        while ($remaining > 0 && !$stream->eof()) {
            $chunk = $stream->read(\min($remaining, File::DEFAULT_CHUNK_SIZE));
            $remaining -= \strlen($chunk);
        }

        if ($remaining > 0) {
            throw new ToolCallException('Offset is beyond the end of the asset stream.');
        }
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
