<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Endpoint\Search;

use Elastica\Query\MatchAll;
use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Admin\Admin;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Search\Search;
use EMS\CommonBundle\Common\CoreApi\Result;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Elasticsearch\Response\ResponseInterface;
use EMS\CommonBundle\Search\Search as SearchObject;
use PHPUnit\Framework\TestCase;

class SearchAiTest extends TestCase
{
    private Client $client;
    private Search $search;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $admin = $this->createMock(Admin::class);
        $this->search = new Search($this->client, $admin);
    }

    public function testSearch(): void
    {
        $searchObject = new SearchObject(['index1'], new MatchAll());

        $this->client->expects($this->once())
            ->method('post')
            ->with('/api/search/search', ['search' => $searchObject->serialize()])
            ->willReturn($this->createMockResult(['data' => 'test']));

        $response = $this->search->search($searchObject);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testCount(): void
    {
        $searchObject = new SearchObject(['index1'], new MatchAll());

        $this->client->expects($this->once())
            ->method('post')
            ->with('/api/search/count', ['search' => $searchObject->serialize()])
            ->willReturn($this->createMockResult(['count' => 5]));

        $count = $this->search->count($searchObject);

        $this->assertEquals(5, $count);
    }

    public function testVersion(): void
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with('/api/search/version')
            ->willReturn($this->createMockResult(['version' => '1.0.0']));

        $version = $this->search->version();

        $this->assertEquals('1.0.0', $version);
    }

    public function testHealthStatus(): void
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with('/api/search/health-status')
            ->willReturn($this->createMockResult(['status' => 'healthy']));

        $status = $this->search->healthStatus();

        $this->assertEquals('healthy', $status);
    }

    public function testRefresh(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/api/search/refresh', ['index' => 'test_index'])
            ->willReturn($this->createMockResult(['success' => true]));

        $success = $this->search->refresh('test_index');

        $this->assertTrue($success);
    }

    public function testGetIndicesFromAlias(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/api/search/indices-from-alias', ['alias' => 'test_alias'])
            ->willReturn($this->createMockResult(['indices' => ['index1', 'index2']]));

        $indices = $this->search->getIndicesFromAlias('test_alias');

        $this->assertEquals(['index1', 'index2'], $indices);
    }

    public function testGetAliasesFromIndex(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('/api/search/aliases-from-index', ['index' => 'test_index'])
            ->willReturn($this->createMockResult(['aliases' => ['alias1', 'alias2']]));

        $aliases = $this->search->getAliasesFromIndex('test_index');

        $this->assertEquals(['alias1', 'alias2'], $aliases);
    }

    public function testGetDocument(): void
    {
        $documentData = [
            '_index' => 'test_index',
            '_id' => '123',
            '_source' => [
                'field' => 'value',
                '_contenttype' => 'test_content_type',
            ],
        ];

        $this->client->expects($this->once())
            ->method('post')
            ->with('/api/search/document', [
                'index' => 'test_index',
                'content-type' => 'test_content_type',
                'ouuid' => '123',
                'source-includes' => [],
                'sources-excludes' => [],
            ])
            ->willReturn($this->createMockResult($documentData));

        $document = $this->search->getDocument('test_index', 'test_content_type', '123');

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('123', $document->getId());
    }

    private function createMockResult(array $data): Result
    {
        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn($data);

        return $result;
    }
}
