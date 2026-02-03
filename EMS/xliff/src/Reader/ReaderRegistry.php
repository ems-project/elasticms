<?php

declare(strict_types=1);

namespace EMS\Xliff\Reader;

class ReaderRegistry
{
    /** @param ReaderInterface[] $readers */
    public function __construct(private readonly array $readers)
    {
    }

    public function detect(string $xml): ReaderInterface
    {
        foreach ($this->readers as $reader) {
            if ($reader->supports($xml)) {
                return $reader;
            }
        }
        throw new \InvalidArgumentException('Unsupported or unrecognized XLIFF document.');
    }
}
