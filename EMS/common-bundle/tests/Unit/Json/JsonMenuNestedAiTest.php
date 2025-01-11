<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Json;

use EMS\CommonBundle\Json\JsonMenuNested;
use EMS\CommonBundle\Json\JsonMenuNestedException;
use PHPUnit\Framework\TestCase;

class JsonMenuNestedAiTest extends TestCase
{
    private JsonMenuNested $menu;

    #[\Override]
    protected function setUp(): void
    {
        $this->menu = new JsonMenuNested([
            'id' => 'root',
            'type' => 'menu',
            'label' => 'Root Menu',
            'children' => [
                ['id' => 'child1', 'type' => 'submenu', 'label' => 'Child 1'],
                ['id' => 'child2', 'type' => 'submenu', 'label' => 'Child 2'],
            ],
        ]);
    }

    public function testConstructorAndBasicGetters(): void
    {
        $this->assertEquals('root', $this->menu->getId());
        $this->assertEquals('menu', $this->menu->getType());
        $this->assertEquals('Root Menu', $this->menu->getLabel());
        $this->assertCount(2, $this->menu);
    }

    public function testCreateMethod(): void
    {
        $menu = JsonMenuNested::create('type', ['label' => 'New Menu']);
        $this->assertEquals('type', $menu->getType());
        $this->assertEquals('New Menu', $menu->getLabel());
    }

    public function testFromStructureMethod(): void
    {
        $structure = [
            ['id' => 'child1', 'type' => 'submenu', 'label' => 'Child 1'],
            ['id' => 'child2', 'type' => 'submenu', 'label' => 'Child 2'],
        ];
        $menu = JsonMenuNested::fromStructure($structure);

        $this->assertEquals('_root', $menu->getId());
        $this->assertCount(2, $menu);
    }

    public function testExceptionOnInvalidMove(): void
    {
        $this->expectException(JsonMenuNestedException::class);

        $child = new JsonMenuNested(['id' => 'child3', 'type' => 'submenu', 'label' => 'Child 3']);
        $newParent = new JsonMenuNested(['id' => 'newParent', 'type' => 'menu', 'label' => 'New Parent']);

        $this->menu->moveChild($child, $this->menu, $newParent, 0);
    }

    public function testIteratorAggregate(): void
    {
        foreach ($this->menu as $child) {
            $this->assertInstanceOf(JsonMenuNested::class, $child);
        }
    }

    public function testCountable(): void
    {
        $this->assertCount(2, $this->menu);
    }

    public function testToArray(): void
    {
        $array = $this->menu->toArray();
        $this->assertIsArray($array);
        $this->assertCount(3, $array);
    }

    public function testToArrayStructure(): void
    {
        $structure = $this->menu->toArrayStructure();
        $this->assertIsArray($structure);

        foreach ($structure as $item) {
            $this->assertIsArray($item);
            $this->assertArrayHasKey('children', $item);
        }
    }

    public function testFilterChildren(): void
    {
        $filtered = $this->menu->filterChildren(fn ($child) => 'child1' === $child->getId());
        $this->assertCount(1, $filtered);
    }

    public function testDiffChildren(): void
    {
        $newMenu = new JsonMenuNested([
            'id' => 'root',
            'type' => 'menu',
            'label' => 'Root Menu Updated',
            'children' => [
                ['id' => 'child1', 'type' => 'submenu', 'label' => 'Child 1 Updated'],
                ['id' => 'child3', 'type' => 'submenu', 'label' => 'Child 3'],
            ],
        ]);

        $diff = $this->menu->diffChildren($newMenu);
        $this->assertCount(1, $diff);
    }

    public function testChangeId(): void
    {
        $originalId = $this->menu->getId();
        $this->menu->changeId();
        $this->assertNotEquals($originalId, $this->menu->getId());
    }

    public function testGetItemById(): void
    {
        $item = $this->menu->getItemById('child1');
        $this->assertNotNull($item);
        $this->assertEquals('Child 1', $item->getLabel());
    }

    public function testGiveItemById(): void
    {
        $item = $this->menu->giveItemById('child1');
        $this->assertEquals('Child 1', $item->getLabel());
    }

    public function testExceptionOnGiveItemById(): void
    {
        $this->expectException(JsonMenuNestedException::class);
        $this->menu->giveItemById('nonexistent');
    }

    public function testSetAndGetLabel(): void
    {
        $this->menu->setLabel('Updated Root Menu');
        $this->assertEquals('Updated Root Menu', $this->menu->getLabel());
    }

    public function testSetAndGetParent(): void
    {
        $parent = new JsonMenuNested(['id' => 'newParent', 'type' => 'menu', 'label' => 'New Parent']);
        $this->menu->setParent($parent);
        $this->assertEquals($parent, $this->menu->getParent());
    }

    public function testBreadcrumb(): void
    {
        $breadcrumbs = \iterator_to_array($this->menu->breadcrumb('child1'));
        $this->assertCount(1, $breadcrumbs);
        $this->assertEquals('child1', $breadcrumbs[0]->getId());
    }
}
