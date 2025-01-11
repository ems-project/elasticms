<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

/**
 * @implements \IteratorAggregate<int, mixed>
 */
class CsvFile implements \Countable, \IteratorAggregate
{
    public const DEFAULT_DELIMITER = ',';
    private const string UTF8_BOM = "\xEF\xBB\xBF";

    public function __construct(
        private readonly string $filename,
        private readonly string $delimiter,
        private readonly ?string $encoding = null,
    ) {
    }

    /**
     * @yield array<int, mixed>
     */
    #[\Override]
    public function getIterator(): \Generator
    {
        foreach ($this->getRows() as $row) {
            if ($this->encoding) {
                $row = \array_map([$this, 'convertEncoding'], $row);
            }
            yield $row;
        }
    }

    #[\Override]
    public function count(): int
    {
        $count = 0;
        $handle = $this->getHandle();
        while (false !== \fgetcsv($handle, 2000, $this->delimiter)) {
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

    private function convertEncoding(mixed $value): mixed
    {
        return \is_string($value) ? \mb_convert_encoding($value, 'UTF-8', $this->encoding) : $value;
    }

    /**
     * @yield array<int, mixed>
     */
    private function getRows(): \Generator
    {
        $handle = $this->getHandle();

        $firstRow = \fgets($handle);
        if (false !== $firstRow) {
            if (0 === \strncmp($firstRow, self::UTF8_BOM, 3)) {
                $firstRow = \substr($firstRow, 3);
            }
            yield \str_getcsv($firstRow, $this->delimiter);
        }

        while (($row = \fgetcsv($handle, 2000, $this->delimiter)) !== false) {
            yield $row;
        }
        \fclose($handle);
    }
}
