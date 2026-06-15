<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Mcp;

use EMS\CoreBundle\Service\UserService;
use Mcp\Exception\ToolCallException;
use Psr\Log\LoggerInterface;

final readonly class ElasticmsMcpToolUserService
{
    public function __construct(
        private UserService $userService,
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
}
