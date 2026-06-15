<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use EMS\CoreBundle\Core\ContentType\ContentTypeRoles;
use EMS\CoreBundle\Entity\AuthToken;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Entity\Environment;
use EMS\CoreBundle\Entity\FieldType;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Entity\User;
use EMS\CoreBundle\Form\DataField\ChoiceFieldType;
use EMS\CoreBundle\Form\DataField\CollectionFieldType;
use EMS\CoreBundle\Form\DataField\NestedFieldType;
use EMS\CoreBundle\Form\DataField\TextStringFieldType;
use EMS\CoreBundle\Tests\Integration\App\Kernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class McpControllerTest extends WebTestCase
{
    private const string API_TOKEN = 'elasticms-mcp-token';

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();

        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();

        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testInitializeRequiresValidBearerToken(): void
    {
        $this->client->request(
            'POST',
            '/api/mcp',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer invalid-token',
                'HTTP_HOST' => 'localhost',
            ],
            content: $this->jsonEncode($this->initializePayload())
        );

        self::assertResponseStatusCodeSame(401);
    }

    public function testToolsListExposesPerContentTypeCreateTools(): void
    {
        $this->createAuthenticatedUserWithNewsContent();
        $sessionId = $this->initializeSession($this->client);

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/list',
            'params' => new \stdClass(),
        ];

        $this->client->request(
            'POST',
            '/api/mcp',
            server: $this->mcpHeaders($sessionId),
            content: $this->jsonEncode($payload)
        );

        self::assertResponseIsSuccessful();
        $response = $this->decodeResponse($this->client);
        $tools = $response['result']['tools'] ?? [];
        $toolNames = \array_map(static fn (array $tool): string => (string) $tool['name'], $tools);

        self::assertContains('get_current_user', $toolNames);
        self::assertContains('get_document_news', $toolNames);
        self::assertContains('create_document_news', $toolNames);
        self::assertContains('get_document_secret', $toolNames);
        self::assertNotContains('create_document_secret', $toolNames);

        $createNewsTool = \array_values(\array_filter($tools, static fn (array $tool): bool => 'create_document_news' === ($tool['name'] ?? null)))[0] ?? null;

        self::assertIsArray($createNewsTool);
        self::assertSame(['title'], $createNewsTool['inputSchema']['properties']['rawData']['required'] ?? null);
        self::assertSame('string', $createNewsTool['inputSchema']['properties']['rawData']['properties']['title']['type'] ?? null);
        self::assertSame('object', $createNewsTool['inputSchema']['properties']['rawData']['properties']['body']['type'] ?? null);
        self::assertSame('string', $createNewsTool['inputSchema']['properties']['rawData']['properties']['body']['properties']['summary']['type'] ?? null);
        self::assertSame('array', $createNewsTool['inputSchema']['properties']['rawData']['properties']['authors']['type'] ?? null);
        self::assertSame('string', $createNewsTool['inputSchema']['properties']['rawData']['properties']['authors']['items']['properties']['name']['type'] ?? null);
        self::assertSame(['draft', 'published'], $createNewsTool['inputSchema']['properties']['rawData']['properties']['status']['enum'] ?? null);
    }

    public function testToolsCallCanReturnCurrentUserAndCreateContentTypeDraft(): void
    {
        $this->createAuthenticatedUserWithNewsContent();
        $sessionId = $this->initializeSession($this->client);

        $currentUserPayload = [
            'jsonrpc' => '2.0',
            'id' => 3,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_current_user',
                'arguments' => new \stdClass(),
            ],
        ];

        $this->client->request(
            'POST',
            '/api/mcp',
            server: $this->mcpHeaders($sessionId),
            content: $this->jsonEncode($currentUserPayload)
        );

        self::assertResponseIsSuccessful();
        $currentUserResponse = $this->decodeResponse($this->client);
        self::assertArrayHasKey('result', $currentUserResponse, $this->jsonEncode($currentUserResponse));
        $structuredUser = $currentUserResponse['result']['structuredContent']['user'] ?? null;
        self::assertIsArray($structuredUser);
        self::assertSame('mcp-user', $structuredUser['username'] ?? null);

        $createDraftPayload = [
            'jsonrpc' => '2.0',
            'id' => 4,
            'method' => 'tools/call',
            'params' => [
                'name' => 'create_document_news',
                'arguments' => [
                    'rawData' => [
                        'title' => 'MCP News Draft',
                    ],
                ],
            ],
        ];

        $this->client->request(
            'POST',
            '/api/mcp',
            server: $this->mcpHeaders($sessionId),
            content: $this->jsonEncode($createDraftPayload)
        );

        self::assertResponseIsSuccessful();
        $createDraftResponse = $this->decodeResponse($this->client);
        $structuredDraft = $createDraftResponse['result']['structuredContent'] ?? null;

        self::assertIsArray($structuredDraft);
        self::assertSame('news', $structuredDraft['contentType'] ?? null);
        self::assertTrue($structuredDraft['draft'] ?? false);
        self::assertSame('MCP News Draft', $structuredDraft['rawData']['title'] ?? null);
        self::assertNotNull($structuredDraft['revisionId'] ?? null);
    }

    public function testGetDocumentUsesAuthenticatedUserPermissions(): void
    {
        $fixtures = $this->createAuthenticatedUserWithNewsContent();
        $sessionId = $this->initializeSession($this->client);

        $payload = [
            'jsonrpc' => '2.0',
            'id' => 5,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_document_news',
                'arguments' => [
                    'ouuid' => $fixtures['revision']->getOuuid(),
                ],
            ],
        ];

        $this->client->request(
            'POST',
            '/api/mcp',
            server: $this->mcpHeaders($sessionId),
            content: $this->jsonEncode($payload)
        );

        self::assertResponseIsSuccessful();
        $response = $this->decodeResponse($this->client);
        $structuredContent = $response['result']['structuredContent'] ?? null;

        self::assertIsArray($structuredContent);
        self::assertSame('news', $structuredContent['contentType'] ?? null);
        self::assertSame($fixtures['revision']->getOuuid(), $structuredContent['ouuid'] ?? null);
        self::assertSame('Published News', $structuredContent['rawData']['title'] ?? null);
    }

    #[\Override]
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    /**
     * @return array{revision: Revision}
     */
    private function createAuthenticatedUserWithNewsContent(): array
    {
        $user = new User();
        $user->setUsername('mcp-user');
        $user->setUsernameCanonical('mcp-user');
        $user->setEmail('mcp@example.test');
        $user->setEmailCanonical('mcp@example.test');
        $user->setEnabled(true);
        $user->setPassword('not-used');
        $user->setRoles(['ROLE_API', 'ROLE_AUTHOR']);

        $authToken = new AuthToken($user)->setValue(self::API_TOKEN);

        $environment = new Environment();
        $environment->setName('preview');
        $environment->setAlias('preview_alias');
        $environment->setManaged(true);
        $environment->setOrderKey(1);

        $contentType = new ContentType()
            ->setName('news')
            ->setSingularName('News')
            ->setPluralName('News')
            ->setActive(true)
            ->setOrderKey(1)
            ->setEnvironment($environment);
        $contentType->setRoles(new ContentTypeRoles([
            ContentTypeRoles::VIEW => 'ROLE_AUTHOR',
            ContentTypeRoles::CREATE => 'ROLE_AUTHOR',
        ]));
        $contentType->getFieldType()->addChild(
            new FieldType()
                ->setName('title')
                ->setType(TextStringFieldType::class)
                ->setOptions([
                    'displayOptions' => [
                        'label' => 'Title',
                    ],
                    'restrictionOptions' => [
                        'mandatory' => true,
                    ],
                ])
        );
        $contentType->getFieldType()->addChild(
            new FieldType()
                ->setName('body')
                ->setType(NestedFieldType::class)
                ->setOptions([
                    'displayOptions' => [
                        'label' => 'Body',
                    ],
                ])
                ->addChild(
                    new FieldType()
                        ->setName('summary')
                        ->setType(TextStringFieldType::class)
                        ->setOptions([
                            'displayOptions' => [
                                'label' => 'Summary',
                            ],
                        ])
                )
        );
        $contentType->getFieldType()->addChild(
            new FieldType()
                ->setName('authors')
                ->setType(CollectionFieldType::class)
                ->setOptions([
                    'displayOptions' => [
                        'label' => 'Authors',
                    ],
                ])
                ->addChild(
                    new FieldType()
                        ->setName('name')
                        ->setType(TextStringFieldType::class)
                        ->setOptions([
                            'displayOptions' => [
                                'label' => 'Name',
                            ],
                        ])
                )
        );
        $contentType->getFieldType()->addChild(
            new FieldType()
                ->setName('status')
                ->setType(ChoiceFieldType::class)
                ->setOptions([
                    'displayOptions' => [
                        'label' => 'Status',
                        'choices' => "draft\npublished",
                    ],
                ])
        );

        $restrictedContentType = new ContentType()
            ->setName('secret')
            ->setSingularName('Secret')
            ->setPluralName('Secrets')
            ->setActive(true)
            ->setOrderKey(2)
            ->setEnvironment($environment);
        $restrictedContentType->setRoles(new ContentTypeRoles([
            ContentTypeRoles::VIEW => 'ROLE_AUTHOR',
            ContentTypeRoles::CREATE => 'ROLE_ADMIN',
        ]));

        $revision = new Revision()
            ->setContentType($contentType)
            ->setDeleted(false)
            ->setDraft(false)
            ->setOuuid('news-1')
            ->setEndTime(null)
            ->setRawData([
                'title' => 'Published News',
            ])
            ->setLockBy('mcp-user')
            ->setLockUntil(new \DateTime('+1 hour'));

        $this->entityManager->persist($user);
        $this->entityManager->persist($environment);
        $this->entityManager->persist($contentType);
        $this->entityManager->persist($restrictedContentType);
        $this->entityManager->persist($revision);
        $this->entityManager->persist($authToken);
        $this->entityManager->flush();

        $this->entityManager->clear();

        /** @var Revision $persistedRevision */
        $persistedRevision = $this->entityManager->getRepository(Revision::class)->find($revision->getId());

        return [
            'revision' => $persistedRevision,
        ];
    }

    private function initializeSession(KernelBrowser $client): string
    {
        $client->request(
            'POST',
            '/api/mcp',
            server: $this->mcpHeaders(),
            content: $this->jsonEncode($this->initializePayload())
        );

        self::assertResponseIsSuccessful();
        $sessionId = $client->getResponse()->headers->get('Mcp-Session-Id');
        self::assertNotNull($sessionId);

        return $sessionId;
    }

    /**
     * @return array{jsonrpc: '2.0', id: int, method: 'initialize', params: array{protocolVersion: string, capabilities: array<mixed>, clientInfo: array{name: string, version: string}}}
     */
    private function initializePayload(): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2025-06-18',
                'capabilities' => [],
                'clientInfo' => [
                    'name' => 'phpunit',
                    'version' => '1.0.0',
                ],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function mcpHeaders(?string $sessionId = null): array
    {
        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.self::API_TOKEN,
            'HTTP_HOST' => 'localhost',
        ];

        if (null !== $sessionId) {
            $headers['HTTP_MCP_SESSION_ID'] = $sessionId;
        }

        return $headers;
    }

    /**
     * @return array<mixed>
     */
    private function decodeResponse(KernelBrowser $client): array
    {
        $decoded = \json_decode($client->getResponse()->getContent() ?: '', true);
        self::assertIsArray($decoded);

        return $decoded;
    }

    /**
     * @param array<mixed> $payload
     */
    private function jsonEncode(array $payload): string
    {
        $encoded = \json_encode($payload, \JSON_THROW_ON_ERROR);

        return \is_string($encoded) ? $encoded : '';
    }
}
