<?php

namespace EMS\Tests\CommonBundle\Unit\Common\Admin;

use EMS\CommonBundle\Common\Admin\ConfigHelper;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\ConfigInterface;
use PHPUnit\Framework\TestCase;

class ConfigHelperAiTest extends TestCase
{
    private $config;
    private $configHelper;
    private $tempDir;

    protected function setUp(): void
    {
        $this->config = $this->createMock(ConfigInterface::class);
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('config_test_', true);
        $this->configHelper = new ConfigHelper($this->config, $this->tempDir);
    }

    protected function tearDown(): void
    {
        // Cleanup temporary directory
        array_map('unlink', glob("$this->tempDir/*.*"));
        rmdir($this->tempDir);
    }

    public function testUpdate(): void
    {
        $this->config->expects($this->once())->method('index')->willReturn(['config1', 'config2']);
        $this->config->expects($this->exactly(2))->method('get')->willReturnOnConsecutiveCalls(['key1' => 'value1'], ['key2' => 'value2']);

        $this->configHelper->update();

        $this->assertFileExists($this->tempDir . DIRECTORY_SEPARATOR . 'config1.json');
        $this->assertFileExists($this->tempDir . DIRECTORY_SEPARATOR . 'config2.json');
    }

    public function testSave(): void
    {
        $configData = ['key' => 'value'];
        $this->configHelper->save('testConfig', $configData);

        $this->assertFileExists($this->tempDir . DIRECTORY_SEPARATOR . 'testConfig.json');
        $this->assertEquals(json_encode($configData, JSON_PRETTY_PRINT), file_get_contents($this->tempDir . DIRECTORY_SEPARATOR . 'testConfig.json'));
    }

    public function testLocal(): void
    {
        touch($this->tempDir . DIRECTORY_SEPARATOR . 'config1.json');
        touch($this->tempDir . DIRECTORY_SEPARATOR . 'config2.json');

        $localConfigs = $this->configHelper->local();

        $this->assertEquals(['config1', 'config2'], $localConfigs);
    }

    public function testRemote(): void
    {
        $this->config->expects($this->once())->method('index')->willReturn(['config1', 'config2']);

        $remoteConfigs = $this->configHelper->remote();

        $this->assertEquals(['config1', 'config2'], $remoteConfigs);
    }

    public function testNeedUpdate(): void
    {
        $this->config->expects($this->exactly(2))->method('get')->willReturnOnConsecutiveCalls(['key1' => 'value1'], ['key2' => 'value2']);

        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . 'config1.json', json_encode(['key1' => 'value1']));
        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . 'config2.json', json_encode(['key2' => 'value2_changed']));

        $configsToUpdate = $this->configHelper->needUpdate(['config1', 'config2']);

        $this->assertEquals(['config2'], $configsToUpdate);
    }

    public function testDeleteConfigs(): void
    {
        $this->config->expects($this->exactly(2))->method('delete');

        $this->configHelper->deleteConfigs(['config1', 'config2']);
    }

    public function testUpdateConfigs(): void
    {
        $this->config->expects($this->exactly(2))->method('update');

        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . 'config1.json', json_encode(['key1' => 'value1']));
        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . 'config2.json', json_encode(['key2' => 'value2']));

        $this->configHelper->updateConfigs(['config1', 'config2']);
    }
}
