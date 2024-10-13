<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\File\FileStructure;

use Psr\Http\Message\StreamInterface;

interface FileStructureClientInterface
{
    public function initSync(string $hash): void;

    public function createFolder(string $path, string $getLabel): void;

    public function createFile(string $path, StreamInterface $stream, string $contentType): void;

    public function finalize(): void;

    public function isUpToDate(): bool;
}
