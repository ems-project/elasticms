<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Json;

use EMS\CommonBundle\Json\JsonMenuNestedException;
use PHPUnit\Framework\TestCase;

class JsonMenuNestedExceptionAiTest extends TestCase
{
    public function testItemNotFound(): void
    {
        $exception = JsonMenuNestedException::itemNotFound();
        $this->assertInstanceOf(JsonMenuNestedException::class, $exception);
        $this->assertEquals('Item not found', $exception->getMessage());
    }

    public function testItemParentNotFound(): void
    {
        $exception = JsonMenuNestedException::itemParentNotFound();
        $this->assertInstanceOf(JsonMenuNestedException::class, $exception);
        $this->assertEquals('Parent not found', $exception->getMessage());
    }

    public function testMoveChildMissing(): void
    {
        $exception = JsonMenuNestedException::moveChildMissing();
        $this->assertInstanceOf(JsonMenuNestedException::class, $exception);
        $this->assertEquals('Move failed, current parent does not have item', $exception->getMessage());
    }

    public function testMoveChildExists(): void
    {
        $exception = JsonMenuNestedException::moveChildExists();
        $this->assertInstanceOf(JsonMenuNestedException::class, $exception);
        $this->assertEquals('Move failed, new parent already has item', $exception->getMessage());
    }
}
