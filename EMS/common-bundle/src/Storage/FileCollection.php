<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage;

/**
 * @implements \IteratorAggregate<array>
 */
final readonly class FileCollection implements \IteratorAggregate
{
    /**
     * @param array<mixed, mixed> $files
     */
    public function __construct(private array $files, private StorageManager $storageManager)
    {
    }

    /**
     * @return \Generator<array<mixed>>
     */
    #[\Override]
    public function getIterator(): \Generator
    {
        foreach ($this->files as $file) {
            $file['stream'] = $this->storageManager->getStream($file['sha1']);
            yield $file;
        }
    }
}
