<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Exception;

use EMS\CommonBundle\Common\CoreApi\Exception\BaseUrlNotDefinedException;
use PHPUnit\Framework\TestCase;

class BaseUrlNotDefinedExceptionAiTest extends TestCase
{
    public function testExceptionDefaultMessage(): void
    {
        $exception = new BaseUrlNotDefinedException();

        $expectedMessage = 'Core api base url not defined!';
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }
}
