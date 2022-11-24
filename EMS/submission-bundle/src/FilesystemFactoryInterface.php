<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;

interface FilesystemFactoryInterface
{
    public function create(FilesystemAdapter $adapter): Filesystem;
}
