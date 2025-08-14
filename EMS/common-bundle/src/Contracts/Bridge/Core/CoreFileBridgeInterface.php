<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Bridge\Core;

interface CoreFileBridgeInterface
{
    public function initUpload(string $hash, int $size, string $filename, string $mimetype): int;

    public function addChunk(string $hash, string $chunk): int;
}
