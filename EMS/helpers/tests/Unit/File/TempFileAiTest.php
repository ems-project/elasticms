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
        $tempFile->clean();
        $this->assertFalse(\file_exists($tempFile->path));
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
        $tempFile->clean();
        $this->assertFalse(\file_exists($tempFile->path));
    }

    public function testClean()
    {
        $tempFile = TempFile::create();
        $tempFile->clean();
        $this->assertFalse(\file_exists($tempFile->path));
    }
}
