<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Mcp;

use EMS\CoreBundle\Core\ContentType\ContentTypeRoles;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Service\ContentTypeService;
use EMS\CoreBundle\Service\DataService;
use EMS\CoreBundle\Service\Revision\RevisionService;
use EMS\CoreBundle\Service\UserService;
use Mcp\Exception\ToolCallException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class ElasticmsMcpToolService
{
    private const string DEFAULT_NEWS_CONTENT_TYPE = 'news';

    public function __construct(
        private UserService $userService,
        private ContentTypeService $contentTypeService,
        private RevisionService $revisionService,
        private DataService $dataService,
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
    public function getContent(string $contentType, string $ouuid): array
    {
        return $this->wrapToolCall('get_content', [
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
    public function createNewsDraft(array $rawData = [], ?string $ouuid = null, ?string $contentType = null): array
    {
        $targetContentType = $contentType ?? self::DEFAULT_NEWS_CONTENT_TYPE;

        return $this->wrapToolCall('create_news_draft', [
            'content_type' => $targetContentType,
            'ouuid' => $ouuid,
            'raw_data_keys' => \array_map('strval', \array_keys($rawData)),
        ], function () use ($rawData, $ouuid, $targetContentType): array {
            $resolvedContentType = $this->contentTypeService->getByName($targetContentType);
            if (false === $resolvedContentType) {
                throw new ToolCallException(\sprintf('News content type "%s" was not found.', $targetContentType));
            }

            try {
                $resolvedContentType->validate();
            } catch (\RuntimeException $exception) {
                throw new ToolCallException($exception->getMessage(), 0, $exception);
            }

            if (!$this->authorizationChecker->isGranted($resolvedContentType->role(ContentTypeRoles::CREATE))) {
                throw new ToolCallException(\sprintf('Create access is not granted for content type "%s".', $targetContentType));
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
     * @param \Closure(): TResult   $callable
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
}
