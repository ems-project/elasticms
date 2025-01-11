<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Endpoint\File;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Common\CoreApi\Endpoint\File\DataExtract;
use EMS\CommonBundle\Common\CoreApi\Result;
use PHPUnit\Framework\TestCase;

class DataExtractAiTest extends TestCase
{
    private Client $client;
    private DataExtract $dataExtract;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->dataExtract = new DataExtract($this->client);
    }

    public function testGet(): void
    {
        $hash = 'sample-hash';
        $expectedData = ['key' => 'value'];

        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn($expectedData);

        $this->client->expects($this->once())
            ->method('get')
            ->with('/api/extract-data/get/'.$hash)
            ->willReturn($result);

        $data = $this->dataExtract->get($hash);

        $this->assertEquals($expectedData, $data);
    }
}
