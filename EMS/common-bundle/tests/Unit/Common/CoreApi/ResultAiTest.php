<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi;

use EMS\CommonBundle\Common\CoreApi\Result;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ResultAiTest extends TestCase
{
    private ResponseInterface $response;
    /**
     * @var Stub&LoggerInterface
     */
    private Stub $logger;

    #[\Override]
    protected function setUp(): void
    {
        $this->response = $this->createMock(ResponseInterface::class);
        $this->logger = $this->createStub(LoggerInterface::class);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetData(): void
    {
        $data = ['someKey' => 'someValue'];
        $this->response->method('getContent')->willReturn(\json_encode($data));

        $result = new Result($this->response, $this->logger);
        $this->assertEquals($data, $result->getData());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testIsSuccess(): void
    {
        $data = ['success' => true];
        $this->response->method('getContent')->willReturn(\json_encode($data));

        $result = new Result($this->response, $this->logger);
        $this->assertTrue($result->isSuccess());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testIsAcknowledged(): void
    {
        $data = ['acknowledged' => true];
        $this->response->method('getContent')->willReturn(\json_encode($data));

        $result = new Result($this->response, $this->logger);
        $this->assertTrue($result->isAcknowledged());
    }
}
