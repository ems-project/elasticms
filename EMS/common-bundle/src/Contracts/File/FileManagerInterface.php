<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\File;

use EMS\CommonBundle\Storage\Archive;
use EMS\CommonBundle\Storage\File\FileInterface;
use Psr\Http\Message\StreamInterface;

interface FileManagerInterface
{
    public const HEADS_CHUNK_SIZE = 256;

    public function downloadFile(string $hash): string;

    public function getFile(string $hash): FileInterface;

    public function getContents(string $hash): string;

    public function getHashAlgo(): string;

    public function getStream(string $hash): StreamInterface;

    /**
     * @return \Traversable<int, string|true>
     */
    public function heads(string ...$fileHashes): \Traversable;

    public function uploadContents(string $contents, string $filename, string $mimeType): string;

    public function uploadFile(string $realPath, ?string $mimeType = null, ?string $filename = null, ?callable $callback = null): string;

    /**
     * @param int<1, max> $chunkSize
     */
    public function setHeadChunkSize(int $chunkSize): void;

    public function loadArchiveItemsInCache(string $archiveHash, Archive $archive, ?callable $callback = null): void;
}
