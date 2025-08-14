<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Bridge\Core;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreFileBridgeInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\File\FileInterface;

readonly class CoreFileApiBridge implements CoreFileBridgeInterface
{
    use CoreBridgeTrait;
    
    public function __construct(private FileInterface $file)
    {
    }

    public function initUpload(string $hash, int $size, string $filename, string $mimetype): int
    {
        return $this->file->initUpload($hash, $size, $filename, $mimetype);
    }

    public function addChunk(string $hash, string $chunk): int
    {
        return $this->file->addChunk($hash, $chunk);
    }
}
