<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\File;

use EMS\Helpers\File\Folder;
use PHPUnit\Framework\TestCase;

class FolderAiTest extends TestCase
{
    private string $testFolderPath;

    protected function setUp(): void
    {
        $this->testFolderPath = \sys_get_temp_dir().DIRECTORY_SEPARATOR.'/path/to/test/folder';
    }

    public function testGetRealPathWithExistingDirectory()
    {
        \mkdir($this->testFolderPath, 0777, true);
        $realPath = Folder::getRealPath($this->testFolderPath);
        $this->assertEquals(\realpath($this->testFolderPath), $realPath);
    }

    public function testGetRealPathWithNonExistingDirectory()
    {
        $realPath = Folder::getRealPath($this->testFolderPath);
        $this->assertEquals(\realpath($this->testFolderPath), $realPath);
    }

    protected function tearDown(): void
    {
        \rmdir($this->testFolderPath);
    }
}
