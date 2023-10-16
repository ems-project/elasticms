<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Exception;

use EMS\CommonBundle\Common\CoreApi\Exception\NotAuthenticatedException;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class NotAuthenticatedExceptionAiTest extends TestCase
{
    public function testExceptionMessageAndCode(): void
    {
        $httpCode = 401;
        $httpMethod = 'GET';
        $url = 'https://example.com/api/resource';

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getInfo')
            ->willReturn([
                'http_code' => $httpCode,
                'http_method' => $httpMethod,
                'url' => $url,
            ]);
        $response->method('getStatusCode')->willReturn($httpCode);

        $exception = new NotAuthenticatedException($response);

        $expectedMessage = \sprintf('%s Unauthorized for [%s] %s', $httpCode, $httpMethod, $url);
        $this->assertEquals($expectedMessage, $exception->getMessage());
        $this->assertEquals($httpCode, $exception->getCode());
    }
}
