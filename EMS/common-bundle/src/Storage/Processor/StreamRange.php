<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Processor;

use Symfony\Component\HttpFoundation\HeaderBag;

class StreamRange
{
    private int $end;
    private int $start = 0;

    public function __construct(HeaderBag $headerBag, private readonly int $fileSize)
    {
        $this->end = $this->fileSize - 1;

        $this->parseRangeHeader($headerBag);

        if ($this->start > $this->end) {
            throw new \RuntimeException('Out of range exception');
        }
    }

    private function parseRangeHeader(HeaderBag $headerBag): void
    {
        $range = $headerBag->get('Range');
        if (null === $range) {
            return;
        }

        [$start, $end] = \explode('-', \substr($range, 6), 2) + [0];

        $this->end = ('' === $end) ? $this->fileSize - 1 : (int) $end;

        if ('' === $start) {
            $this->start = $this->fileSize - $this->end;
            $this->end = $this->fileSize - 1;
        } else {
            $this->start = (int) $start;
        }
    }

    public function isSatisfiable(): bool
    {
        return $this->start >= 0 && $this->end < $this->fileSize;
    }

    public function isPartial(): bool
    {
        return $this->isSatisfiable() && ($this->start > 0 || $this->end < ($this->fileSize - 1));
    }

    public function getContentRangeHeader(): string
    {
        if ($this->isSatisfiable()) {
            return \sprintf('bytes %s-%s/%s', $this->start, $this->end, $this->fileSize);
        }

        return \sprintf('bytes */%s', $this->fileSize);
    }

    public function getContentLengthHeader(): string
    {
        return \strval($this->end - $this->start + 1);
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        return $this->end;
    }
}
