<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage;

/**
 * @implements \IteratorAggregate<array>
 */
final class FileCollection implements \IteratorAggregate
{
    /** @var array<mixed, mixed> */
    private $files;
    /** @var StorageManager */
    private $storageManager;

    /**
     * FileCollection constructor.
     *
     * @param array<mixed, mixed> $files
     */
    public function __construct(array $files, StorageManager $storageManager)
    {
        $this->files = $files;
        $this->storageManager = $storageManager;
    }

    /**
     * @return \Generator<array<mixed>>
     */
    public function getIterator(): \Generator
    {
        foreach ($this->files as $file) {
            $file['stream'] = $this->storageManager->getStream($file['sha1']);
            yield $file;
        }
    }
}
