<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\Elasticsearch\Exception;

use EMS\CommonBundle\Elasticsearch\Exception\NotSingleResultException;
use PHPUnit\Framework\TestCase;

final class NotSingleResultExceptionAiTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        $exception = new NotSingleResultException(5);
        $this->assertEquals('Not single result exception: 1 result was expected, got 5', $exception->getMessage());
    }

    public function testGetTotal(): void
    {
        $exception = new NotSingleResultException(3);
        $this->assertEquals(3, $exception->getTotal());
    }
}
