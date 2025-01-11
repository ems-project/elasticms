<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\File;

use EMS\Helpers\File\Folder;
use EMS\Helpers\File\TempDirectory;
use PHPUnit\Framework\TestCase;

class FolderAiTest extends TestCase
{
    private string $testFolderPath;

    #[\Override]
    protected function setUp(): void
    {
        $this->testFolderPath = TempDirectory::create()->path;
    }

    public function testGetRealPathWithExistingDirectory()
    {
        $realPath = Folder::getRealPath($this->testFolderPath);
        $this->assertEquals(\realpath($this->testFolderPath), $realPath);
    }

    public function testGetRealPathWithNonExistingDirectory()
    {
        $realPath = Folder::getRealPath($this->testFolderPath);
        $this->assertEquals(\realpath($this->testFolderPath), $realPath);
    }

    #[\Override]
    protected function tearDown(): void
    {
        \rmdir($this->testFolderPath);
    }
}
