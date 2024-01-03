<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\File;

use Psr\Http\Message\StreamInterface;

interface FileInterface
{
    public function downloadFile(string $hash): string;

    public function downloadLink(string $hash): string;

    public function hashFile(string $filename): string;

    public function hashStream(StreamInterface $stream): string;

    public function initUpload(string $hash, int $size, string $filename, string $mimetype): int;

    public function addChunk(string $hash, string $chunk): int;

    public function uploadContents(string $contents, string $filename, string $mimeType): string;

    public function uploadFile(string $realPath, ?string $mimeType = null, ?string $filename = null, ?callable $callback = null): string;

    public function uploadStream(StreamInterface $stream, string $filename, string $mimeType): string;

    public function headFile(string $realPath): bool;

    public function headHash(string $hash): bool;
}
