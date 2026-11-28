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

        if (true !== ($options['all_sheets'] ?? false)) {
            return $reader->load($filename)->getActiveSheet()->toArray();
        }

        $data = [];
        foreach ($reader->load($filename)->getAllSheets() as $sheet) {
            $data[$sheet->getTitle()] = $sheet->toArray();
        }

        return $data;
    }

    #[\Override]
    public function readCells(string $filename, array $options = []): \Generator
    {
        $mimeType = $options['mime_type'] ?? null;
        $csvExtension = 0 === \strcasecmp(\pathinfo($filename, PATHINFO_EXTENSION), 'csv');

        if ($csvExtension || 'text/csv' === $mimeType) {
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
                $headings = \array_map(static fn ($v, $k) => ('' === u($v)->trim()->toString()) ? (string) $k : u($v)->trim()->toString(), $row, \array_keys($row));
                continue;
            }

            if (\count($headings) !== \count($row)) {
                $invalid[] = $row;
                continue;
            }

            $rowData = \array_filter(\array_combine($headings, $row), static fn ($v) => '' !== $v && null !== $v);
            if ([] !== $rowData) {
                yield $rowData;
            }

            if ($limit && 0 === --$limit) {
                break;
            }
        }

        return $invalid;
    }
}
