<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\Elasticsearch\Exception;

use EMS\CommonBundle\Elasticsearch\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;

final class NotFoundExceptionAiTest extends TestCase
{
    public function testExceptionMessageWithOuuidAndIndex(): void
    {
        $exception = new NotFoundException('1234', 'test_index');
        $this->assertEquals('Document 1234 not found in index/alias test_index', $exception->getMessage());
    }

    public function testExceptionMessageWithOuuidOnly(): void
    {
        $exception = new NotFoundException('1234');
        $this->assertEquals('Document 1234 not found', $exception->getMessage());
    }

    public function testDefaultExceptionMessage(): void
    {
        $exception = new NotFoundException();
        $this->assertEquals('Not found exception', $exception->getMessage());
    }
}
