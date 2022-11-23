<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests;

use EMS\SubmissionBundle\FilesystemFactory;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase;

final class FilesystemFactoryTest extends TestCase
{
    private FilesystemFactory $filesystemFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystemFactory = new FilesystemFactory();
    }

    public function testCreate()
    {
        $adapter = new LocalFilesystemAdapter('.');
        $filesystem = $this->filesystemFactory->create($adapter);
        $this->assertInstanceOf(Filesystem::class, $filesystem);
    }
}
