<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\File;

interface FileManagerInterface
{
    public const HEADS_CHUNK_SIZE = 1000;

    public function downloadFile(string $hash): string;

    public function getHashAlgo(): string;

    /**
     * @return \Traversable<int, string>
     */
    public function heads(string ...$fileHashes): \Traversable;

    public function uploadContents(string $contents, string $filename, string $mimeType): string;

    public function uploadFile(string $realPath, ?string $mimeType = null, ?string $filename = null, ?callable $callback = null): string;
}
