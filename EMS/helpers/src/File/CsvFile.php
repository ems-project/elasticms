<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

/**
 * @implements \IteratorAggregate<int, mixed>
 */
class CsvFile implements \Countable, \IteratorAggregate
{
    private const DEFAULT_DELIMITER = ',';

    public function __construct(
        private readonly string $filename,
        private readonly ?string $delimiter = null,
        private readonly ?string $encoding = null,
    ) {
    }

    /**
     * @yield array<int, mixed>
     */
    public function getIterator(): \Generator
    {
        $handle = $this->getHandle();
        while (($row = \fgetcsv($handle, 2000, $this->getDelimiter())) !== false) {
            if ($this->encoding) {
                $row = \array_map([$this, 'convertEncoding'], $row);
            }
            yield $row;
        }
        \fclose($handle);
    }

    public function count(): int
    {
        $count = 0;
        $handle = $this->getHandle();
        while (false !== \fgetcsv($handle, 2000, $this->getDelimiter())) {
            ++$count;
        }
        \fclose($handle);

        return $count;
    }

    /**
     * @return resource
     */
    private function getHandle()
    {
        if (false === $handle = \fopen($this->filename, 'rb')) {
            throw new \RuntimeException(\sprintf('Could not open "%s"', $this->filename));
        }

        return $handle;
    }

    private function getDelimiter(): string
    {
        return $this->delimiter ?? self::DEFAULT_DELIMITER;
    }

    private function convertEncoding(mixed $value): mixed
    {
        return \is_string($value) ? \mb_convert_encoding($value, 'UTF-8', $this->encoding) : $value;
    }
}
