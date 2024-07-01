<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage;

use Psr\Http\Message\StreamInterface;

class StreamWrapper
{
    public function __construct(private readonly StreamInterface $stream, private readonly string $mimetype, private readonly int $size)
    {
    }

    public function getStream(): StreamInterface
    {
        return $this->stream;
    }

    public function getMimetype(): string
    {
        return $this->mimetype;
    }

    public function getSize(): int
    {
        return $this->size;
    }
}
