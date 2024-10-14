<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\File;

interface FileManagerInterface
{
    public function downloadFile(string $hash): string;

    public function getHashAlgo(): string;

    /**
     * @return iterable<string>
     */
    public function heads(string ...$fileHashes): iterable;

    public function uploadContents(string $contents, string $filename, string $mimeType): string;

    public function uploadFile(string $realPath, ?string $mimeType = null, ?string $filename = null, ?callable $callback = null): string;
}
