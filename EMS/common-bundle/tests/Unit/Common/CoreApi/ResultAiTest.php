<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi;

use EMS\CommonBundle\Common\CoreApi\Result;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ResultAiTest extends TestCase
{
    private ResponseInterface $response;
    private LoggerInterface $logger;

    #[\Override]
    protected function setUp(): void
    {
        $this->response = $this->createMock(ResponseInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testGetData(): void
    {
        $data = ['someKey' => 'someValue'];
        $this->response->method('getContent')->willReturn(\json_encode($data));

        $result = new Result($this->response, $this->logger);
        $this->assertEquals($data, $result->getData());
    }

    public function testIsSuccess(): void
    {
        $data = ['success' => true];
        $this->response->method('getContent')->willReturn(\json_encode($data));

        $result = new Result($this->response, $this->logger);
        $this->assertTrue($result->isSuccess());
    }

    public function testIsAcknowledged(): void
    {
        $data = ['acknowledged' => true];
        $this->response->method('getContent')->willReturn(\json_encode($data));

        $result = new Result($this->response, $this->logger);
        $this->assertTrue($result->isAcknowledged());
    }
}
