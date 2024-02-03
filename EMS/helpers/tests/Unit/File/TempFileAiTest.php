<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\File;

use EMS\Helpers\File\TempFile;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class TempFileAiTest extends TestCase
{
    public function testCreate()
    {
        $tempFile = TempFile::create();
        $this->assertInstanceOf(TempFile::class, $tempFile);
        $this->assertTrue(\file_exists($tempFile->path));
    }

    public function testCreateNamed()
    {
        $name = 'testfile.txt';
        $tempFile = TempFile::createNamed($name);
        $this->assertInstanceOf(TempFile::class, $tempFile);
        $this->assertEquals(\sys_get_temp_dir().DIRECTORY_SEPARATOR.'EMS_temp_file_'.$name, $tempFile->path);
    }

    public function testLoadFromStream()
    {
        $tempFile = TempFile::create();
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('eof')->willReturnOnConsecutiveCalls(false, true);
        $stream->method('read')->willReturn('Test content');

        $tempFile->loadFromStream($stream);
        $this->assertTrue(\file_exists($tempFile->path));
        $this->assertEquals('Test content', \file_get_contents($tempFile->path));
    }

    public function testClean()
    {
        $tempFile = TempFile::create();
        $tempFile->clean();
        $this->assertFalse(\file_exists($tempFile->path));
    }
}
