<?php

declare(strict_types=1);

namespace EMS\Xliff\Writer;

final readonly class WriterRegistry
{
    /** @param WriterInterface[] $writers */
    public function __construct(private array $writers)
    {
    }

    public function forVersion(string $version): WriterInterface
    {
        foreach ($this->writers as $w) {
            if ($w->supportsVersion($version)) {
                return $w;
            }
        }
        throw new \InvalidArgumentException('Unsupported XLIFF version: '.$version);
    }
}
