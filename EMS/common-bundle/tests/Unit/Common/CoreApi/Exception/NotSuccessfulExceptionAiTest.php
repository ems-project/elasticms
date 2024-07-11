<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Exception;

use EMS\CommonBundle\Common\CoreApi\Exception\NotSuccessfulException;
use EMS\CommonBundle\Common\CoreApi\Result;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class NotSuccessfulExceptionAiTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getInfo')
            ->willReturn([
                'http_method' => 'GET',
                'url' => 'https://example.com/api/test',
            ]);
        $response->method('getStatusCode')->willReturn(404);
        $response->method('getContent')->willReturn('{}');

        $logger = $this->createMock(LoggerInterface::class);
        $result = new Result($response, $logger);

        $exception = new NotSuccessfulException($result);
        $expectedMessage = '[GET] https://example.com/api/test was not successful! (Check logs!)';
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
    }
}
