<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Mcp;

use EMS\CoreBundle\Entity\McpTool;
use EMS\CoreBundle\Service\ContentTypeService;
use EMS\CoreBundle\Service\Mcp\McpToolService;
use EMS\CoreBundle\Service\UserService;
use EMS\Helpers\Standard\Json;
use Mcp\Exception\ToolCallException;
use Mcp\Server\Builder;
use Psr\Log\LoggerInterface;
use Twig\Environment;

final class ElasticmsMcpToolCustomService extends AbstractElasticmsMcpToolService
{
    /** @var array<string, mixed[]> */
    private ?array $contentTypeSchemas = null;

    public function __construct(
        UserService $userService,
        private McpToolService $mcpToolService,
        private ContentTypeService $contentTypeService,
        private ElasticmsMcpToolDataService $toolDataService,
        private Environment $twig,
        LoggerInterface $logger,
        LoggerInterface $auditLogger,
    ) {
        parent::__construct($userService, $logger, $auditLogger);
    }

    public function addCustomTools(Builder $builder): void
    {
        foreach ($this->mcpToolService->getAll() as $mcpTool) {
            if (!$this->isGranted($mcpTool)) {
                continue;
            }

            $builder->addTool(
                handler: fn (mixed ...$arguments): mixed => $this->callCustomTool($mcpTool, $arguments),
                name: $mcpTool->getName(),
                description: $mcpTool->getDescription() ?? $mcpTool->getLabel(),
                inputSchema: $this->buildInputSchema($mcpTool),
                outputSchema: $this->buildOutputSchema($mcpTool),
            );
        }
    }

    /**
     * @param mixed[] $arguments
     */
    private function callCustomTool(McpTool $mcpTool, array $arguments): mixed
    {
        return $this->wrapToolCall($mcpTool->getName(), $arguments, function () use ($mcpTool, $arguments): mixed {
            $template = $mcpTool->getResponse();
            if (null === $template || '' === \trim($template)) {
                throw new ToolCallException(\sprintf('MCP tool "%s" has no Response Twig template configured.', $mcpTool->getName()));
            }
            $rendered = $this->twig->createTemplate($template)->render([
                'tool' => $mcpTool,
                ...$arguments,
            ]);
            dump($rendered);

            return Json::decode($rendered);
        });
    }

    private function isGranted(McpTool $mcpTool): bool
    {
        if (!$mcpTool->isEnabled()) {
            return false;
        }

        return $this->userService->isGrantedRole($mcpTool->getRole());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildInputSchema(McpTool $mcpTool): array
    {
        $template = $mcpTool->getInputSchema();
        if (null === $template || '' === \trim($template)) {
            throw new ToolCallException(\sprintf('MCP tool "%s" has no Input Schema Twig template configured.', $mcpTool->getName()));
        }
        $rendered = $this->twig->createTemplate($template)->render([
            'tool' => $mcpTool,
        ]);

        return Json::decode($rendered);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOutputSchema(McpTool $mcpTool): array
    {
        $template = $mcpTool->getOutputSchema();
        if (null === $template || '' === \trim($template)) {
            throw new ToolCallException(\sprintf('MCP tool "%s" has no Output Schema Twig template configured.', $mcpTool->getName()));
        }
        $rendered = $this->twig->createTemplate($template)->render([
            'tool' => $mcpTool,
            'contentTypeSchemas' => $this->getContentTypeSchemas(),
        ]);

        return Json::decode($rendered);
    }

    /**
     * @return array<string, mixed[]>
     */
    private function getContentTypeSchemas(): array
    {
        if (null !== $this->contentTypeSchemas) {
            return $this->contentTypeSchemas;
        }
        $this->contentTypeSchemas = [];
        foreach ($this->contentTypeService->getAll() as $contentType) {
            $this->contentTypeSchemas[$contentType->getName()] = $this->toolDataService->buildGetDocumentOutputSchema($contentType, true);
        }

        return $this->contentTypeSchemas;
    }
}
