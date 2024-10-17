<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\File;

use EMS\CommonBundle\Contracts\File\FileManagerInterface;
use Psr\Http\Message\StreamInterface;

interface FileInterface extends FileManagerInterface
{
    public function downloadLink(string $hash): string;

    public function hashFile(string $filename): string;

    public function hashStream(StreamInterface $stream): string;

    public function initUpload(string $hash, int $size, string $filename, string $mimetype): int;

    public function addChunk(string $hash, string $chunk): int;

    public function uploadStream(StreamInterface $stream, string $filename, string $mimeType, bool $head = true): string;

    public function headFile(string $realPath): bool;

    public function headHash(string $hash): bool;
}
