<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Json;

use EMS\CommonBundle\Json\JsonMenu;
use PHPUnit\Framework\TestCase;

class JsonMenuAiTest extends TestCase
{
    private JsonMenu $jsonMenu;
    private string $sampleJson;

    #[\Override]
    protected function setUp(): void
    {
        $this->sampleJson = '[{"id":"1","label":"Home","type":"page","children":[{"id":"2","label":"About","type":"page"}]}]';
        $this->jsonMenu = new JsonMenu($this->sampleJson, '/');
    }

    public function testConstructorAndBasicGetters(): void
    {
        $this->assertEquals($this->sampleJson, $this->jsonMenu->getJson());
        $this->assertEquals('/', $this->jsonMenu->getGlue());
        $this->assertIsArray($this->jsonMenu->getStructure());
    }

    public function testConvertJsonMenuNested(): void
    {
        $nestedJson = $this->jsonMenu->convertJsonMenuNested();
        $this->assertJson($nestedJson);
        // Additional assertions to check the structure of the nested JSON
    }

    public function testGetBySlug(): void
    {
        $expected = ['id' => '2', 'label' => 'About', 'type' => 'page'];
        $result = $this->jsonMenu->getBySlug('Home/About');
        $this->assertEquals($expected, $result);
    }

    public function testGetSlug(): void
    {
        $this->assertEquals('Home/About', $this->jsonMenu->getSlug('2'));
    }

    public function testGetItem(): void
    {
        $expected = ['id' => '2', 'label' => 'About', 'type' => 'page'];
        $result = $this->jsonMenu->getItem('2');
        $this->assertEquals($expected, $result);
    }

    public function testGetUids(): void
    {
        $this->assertEquals(['1', '2'], $this->jsonMenu->getUids());
    }

    public function testGetSlugs(): void
    {
        $this->assertEquals(['Home', 'Home/About'], $this->jsonMenu->getSlugs());
    }

    public function testContains(): void
    {
        $this->assertTrue($this->jsonMenu->contains('1'));
        $this->assertFalse($this->jsonMenu->contains('nonexistent'));
    }

    public function testEmptyJson(): void
    {
        $emptyMenu = new JsonMenu('[]', '/');
        $this->assertEmpty($emptyMenu->getStructure());
    }

    public function testInvalidJson(): void
    {
        $this->expectException(\Throwable::class);
        new JsonMenu('invalid json', '/');
    }
}
