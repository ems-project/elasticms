<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Exception;

use EMS\CommonBundle\Common\CoreApi\Exception\NotSuccessfulException;
use PHPUnit\Framework\TestCase;
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
        $response->method('getStatusCode')
            ->willReturn(404);

        $exception = new NotSuccessfulException($response);

        $expectedMessage = '[GET] https://example.com/api/test was not successful! (Check logs!)';
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
    }
}
