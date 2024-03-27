<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\File;

interface FileGetterInterface
{
    public function getContent(): string;

    public function getFilename(): string;

    public function close(): void;
}
