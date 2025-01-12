<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\File;

use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use EMS\Helpers\File\CsvFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

use function Symfony\Component\String\u;

final class FileReader implements FileReaderInterface
{
    #[\Override]
    public function getData(string $filename, array $options = []): array
    {
        $reader = IOFactory::createReaderForFile($filename);

        $encoding = $options['encoding'] ?? null;
        if ($reader instanceof Csv && null !== $encoding) {
            $reader->setInputEncoding($encoding);
        }

        if ($reader instanceof Csv && isset($options['delimiter'])) {
            $reader->setDelimiter($options['delimiter']);
        }

        return $reader->load($filename)->getActiveSheet()->toArray();
    }

    #[\Override]
    public function readCells(string $filename, array $options = []): \Generator
    {
        $isCsv = 0 === \strcasecmp(\pathinfo($filename, PATHINFO_EXTENSION), 'csv');

        if ($isCsv) {
            $csv = new CsvFile(
                filename: $filename,
                delimiter: ($options['delimiter'] ?? CsvFile::DEFAULT_DELIMITER),
                encoding: ($options['encoding'] ?? null)
            );
            $total = \count($csv);
            $data = $csv;
        } else {
            $data = $this->getData($filename, $options);
            $total = \count($data);
        }

        $excludeRows = ($options['exclude_rows'] ?? []);
        $excludeIndexes = \array_map(static fn (int $i) => $i < 0 ? $total + $i : $i, $excludeRows);
        $headings = false;
        $invalid = [];
        $limit = $options['limit'] ?? false;

        foreach ($data as $index => $row) {
            if (\in_array($index, $excludeIndexes, true)) {
                continue;
            }

            if (!$headings) {
                $headings = \array_map(static fn ($v, $k) => ('' === u($v)->trim()->toString()) ? \strval($k) : u($v)->trim()->toString(), $row, \array_keys($row));
                continue;
            }

            if (\count($headings) !== \count($row)) {
                $invalid[] = $row;
                continue;
            }

            $rowData = \array_filter(\array_combine($headings, $row), static fn ($v) => '' !== $v && null !== $v);
            if (\count($rowData) > 0) {
                yield $rowData;
            }

            if ($limit && 0 === --$limit) {
                break;
            }
        }

        return $invalid;
    }
}
