<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Json;

use EMS\CommonBundle\Json\JsonMenuNested;
use PHPUnit\Framework\TestCase;

class JsonMenuNestedTest extends TestCase
{
    private JsonMenuNested $jsonMenuNested1;
    private JsonMenuNested $jsonMenuNested2;

    #[\Override]
    protected function setUp(): void
    {
        $this->jsonMenuNested1 = JsonMenuNested::fromStructure(\file_get_contents(__DIR__.'/json_menu_nested_1.json'));
        $this->jsonMenuNested2 = JsonMenuNested::fromStructure(\file_get_contents(__DIR__.'/json_menu_nested_2.json'));
    }

    public function testMethods(): void
    {
        $this->assertSame('_root', $this->jsonMenuNested1->getId());
        $this->assertSame('_root', $this->jsonMenuNested1->getLabel());

        $player1Item = $this->jsonMenuNested1->getItemById('c7b74edf-5cd1-4af8-a5f0-2ae8fdcc1540');

        $this->assertSame('test player 1', $player1Item->getLabel());
        $this->assertSame('player', $player1Item->getType());
    }

    public function testLoopJsonMenuNested(): void
    {
        $this->assertTrue($this->jsonMenuNested1->hasChildren());
        $this->assertTrue($this->jsonMenuNested1->isRoot());

        $count = 0;
        foreach ($this->jsonMenuNested1 as $item) {
            $this->assertInstanceOf(JsonMenuNested::class, $item);
            ++$count;
        }
        $this->assertEquals(6, $count);
    }

    public function testHasChild()
    {
        $children = $this->jsonMenuNested1->getChildren();

        foreach ($children as $child) {
            $this->assertTrue($this->jsonMenuNested1->hasChild($child));
        }
    }

    public function testChangeId()
    {
        $this->assertEquals('_root', $this->jsonMenuNested1->getId());
        $player1 = $this->jsonMenuNested1->getItemById('c7b74edf-5cd1-4af8-a5f0-2ae8fdcc1540');

        $this->jsonMenuNested1->changeIds();

        $this->assertEquals('_root', $this->jsonMenuNested1->getId());
        $this->assertNotEquals('c7b74edf-5cd1-4af8-a5f0-2ae8fdcc1540', $player1->getId());
    }

    public function testPathMap()
    {
        $callback = fn (JsonMenuNested $p) => $p->getLabel();
        $player1 = $this->jsonMenuNested1->getItemById('c7b74edf-5cd1-4af8-a5f0-2ae8fdcc1540');

        $this->assertSame(['_root'], $this->jsonMenuNested1->getPath($callback));
        $this->assertSame(['League 1', 'club 1', 'test player 1'], $player1->getPath($callback));
    }

    public function testDiff(): void
    {
        $movedPlayer = new JsonMenuNested(['id' => '6fccb8ac-1ce6-41ee-b7e5-c83ccdb6c674', 'label' => 'player moved', 'type' => 'player']);

        $club1 = $this->jsonMenuNested1->getItemById('77d39331-88ff-4335-b249-9abb09264af7');
        $club2 = $this->jsonMenuNested2->getItemById('032f6b45-8f4e-4fe2-8d42-2df4afa08c7c');

        $club1->addChild($movedPlayer);
        $club2->addChild($movedPlayer);

        $callback = fn (JsonMenuNested $item) => 'player' === $item->getType();
        $players1 = $this->jsonMenuNested1->filterChildren($callback);
        $players2 = $this->jsonMenuNested2->filterChildren($callback);

        $printLabel = fn (JsonMenuNested $item) => $item->getLabel();

        $diff1Children = $players1->diffChildren($players2)->getChildren($printLabel);
        $diff2Children = $players2->diffChildren($players1)->getChildren($printLabel);

        self::assertSame(['player moved', 'test player 2'], $diff1Children, 'Removal and movement');
        self::assertSame(['test player 3', 'player moved'], $diff2Children, 'Added and movement');
    }

    public function testDiffItem2RemovedFromMenuB()
    {
        $menuA = new JsonMenuNested(['id' => 'menuA', 'label' => 'menuA', 'type' => 'menu']);
        $menuB = new JsonMenuNested(['id' => 'menuB', 'label' => 'menuB', 'type' => 'menu']);

        $item1 = new JsonMenuNested(['id' => 'item1', 'label' => 'item 1', 'type' => 'item']);
        $item2 = new JsonMenuNested(['id' => 'item2', 'label' => 'item 2', 'type' => 'item']);

        $menuA->addChild($item1)->addChild($item2);
        $menuB->addChild($item1);

        $this->assertSame(['item 2'], $menuA->diffChildren($menuB)->getChildren(fn (JsonMenuNested $c) => $c->getLabel()));
        $this->assertSame([], $menuB->diffChildren($menuA)->getChildren(fn (JsonMenuNested $c) => $c->getLabel()));
    }

    public function testDiffItem2RemovedFromMenuA()
    {
        $menuA = new JsonMenuNested(['id' => 'menuA', 'label' => 'menuA', 'type' => 'menu']);
        $menuB = new JsonMenuNested(['id' => 'menuB', 'label' => 'menuB', 'type' => 'menu']);

        $item1 = new JsonMenuNested(['id' => 'item1', 'label' => 'item 1', 'type' => 'item']);
        $item2 = new JsonMenuNested(['id' => 'item2', 'label' => 'item 2', 'type' => 'item']);

        $menuA->addChild($item1);
        $menuB->addChild($item1)->addChild($item2);

        $this->assertSame([], $menuA->diffChildren($menuB)->getChildren(fn (JsonMenuNested $c) => $c->getLabel()));
        $this->assertSame(['item 2'], $menuB->diffChildren($menuA)->getChildren(fn (JsonMenuNested $c) => $c->getLabel()));
    }
}
