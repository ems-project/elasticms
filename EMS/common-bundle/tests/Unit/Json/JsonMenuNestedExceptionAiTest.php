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
        $this->assertEquals('json_menu_nested.error.item_not_found', $exception->getMessage());
    }

    public function testItemParentNotFound(): void
    {
        $exception = JsonMenuNestedException::itemParentNotFound();
        $this->assertInstanceOf(JsonMenuNestedException::class, $exception);
        $this->assertEquals('json_menu_nested.error.item_parent_not_found', $exception->getMessage());
    }

    public function testMoveChildMissing(): void
    {
        $exception = JsonMenuNestedException::moveChildMissing();
        $this->assertInstanceOf(JsonMenuNestedException::class, $exception);
        $this->assertEquals('json_menu_nested.error.move_child_missing', $exception->getMessage());
    }

    public function testMoveChildExists(): void
    {
        $exception = JsonMenuNestedException::moveChildExists();
        $this->assertInstanceOf(JsonMenuNestedException::class, $exception);
        $this->assertEquals('json_menu_nested.error.move_child_exists', $exception->getMessage());
    }
}
