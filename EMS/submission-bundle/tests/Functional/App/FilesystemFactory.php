<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\App;

use EMS\SubmissionBundle\FilesystemFactoryInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

final class FilesystemFactory implements FilesystemFactoryInterface
{
    /**
     * When raised the create function will add a NullAdapter.
     */
    private bool $flagNullAdapter = true;

    public function create(FilesystemAdapter $adapter): Filesystem
    {
        $adapter = $this->flagNullAdapter ? new InMemoryFilesystemAdapter() : $adapter;

        return new Filesystem($adapter);
    }

    public function setFlagNullAdapter(bool $flag): void
    {
        $this->flagNullAdapter = $flag;
    }
}
