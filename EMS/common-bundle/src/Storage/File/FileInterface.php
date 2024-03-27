<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\File;

interface FileInterface
{
    public function getContent(): string;

    public function getFilename(): string;
}
