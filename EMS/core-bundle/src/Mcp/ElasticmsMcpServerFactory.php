<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Mcp;

use Mcp\Server;
use Mcp\Server\Session\FileSessionStore;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final readonly class ElasticmsMcpServerFactory
{
    public function __construct(
        private ContainerInterface $container,
        private string $cacheDir,
        private LoggerInterface $logger,
        private ElasticmsMcpToolService $toolService,
    ) {
    }

    public function create(): Server
    {
        $builder = Server::builder()
            ->setServerInfo(
                name: 'elasticMS MCP',
                version: '1.0.0',
                description: 'Minimal elasticMS MCP server over HTTP using elasticMS API bearer tokens.',
            )
            ->setInstructions('Authenticate with an elasticMS API bearer token. The server exposes a minimal set of content tools and preserves the authenticated user permissions.')
            ->setContainer($this->container)
            ->setLogger($this->logger)
            ->setSession(new FileSessionStore($this->cacheDir.'/mcp-sessions'))
            ->addTool(
                handler: $this->toolService->getCurrentUser(...),
                name: 'get_current_user',
                description: 'Return the authenticated elasticMS user profile.',
                inputSchema: [
                    'type' => 'object',
                    'additionalProperties' => false,
                ],
            )
            ->addTool(
                handler: $this->toolService->getContent(...),
                name: 'get_content',
                description: 'Read the current content revision for a content type and OUUID, subject to elasticMS permissions.',
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'contentType' => ['type' => 'string'],
                        'ouuid' => ['type' => 'string'],
                    ],
                    'required' => ['contentType', 'ouuid'],
                    'additionalProperties' => false,
                ],
            );
        $this->toolService->addCreateDocumentTools($builder);

        return $builder->build();
    }
}
