<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Endpoint\Meta;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Meta\Meta;
use EMS\CommonBundle\Common\CoreApi\Result;
use PHPUnit\Framework\TestCase;

final class MetaAiTest extends TestCase
{
    private Client $client;
    private Meta $meta;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->meta = new Meta($this->client);
    }

    public function testGetDefaultContentTypeEnvironmentAlias(): void
    {
        $contentTypeName = 'testType';

        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn(['alias' => 'testAlias']);

        $this->client->method('get')
            ->with(\implode('/', ['api', 'meta', 'content-type', $contentTypeName]))
            ->willReturn($result);

        $alias = $this->meta->getDefaultContentTypeEnvironmentAlias($contentTypeName);

        $this->assertEquals('testAlias', $alias);
    }
}
