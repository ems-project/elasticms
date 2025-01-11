<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\File;

use EMS\Helpers\File\File;
use PHPUnit\Framework\TestCase;

class FileAiTest extends TestCase
{
    private const string TEST_FILE_PATH = __DIR__.'/file.txt';

    #[\Override]
    protected function setUp(): void
    {
        \file_put_contents(self::TEST_FILE_PATH, \str_repeat('Test content', 1000));
    }

    #[\Override]
    protected function tearDown(): void
    {
        \unlink(self::TEST_FILE_PATH);
    }

    public function testConstruct()
    {
        $file = new File(new \SplFileInfo(self::TEST_FILE_PATH));
        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals('file.txt', $file->name);
        $this->assertEquals('txt', $file->extension);
        $this->assertIsInt($file->size);
        $this->assertEquals('text/plain', $file->mimeType);
    }

    public function testFromFilename()
    {
        $file = File::fromFilename(self::TEST_FILE_PATH);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testChunk()
    {
        $file = new File(new \SplFileInfo(self::TEST_FILE_PATH));
        $chunks = $file->chunk(0, 1024);

        foreach ($chunks as $chunk) {
            $this->assertLessThanOrEqual(1024, \strlen($chunk));
        }
    }
}
