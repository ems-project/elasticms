<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\File;

use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Reader\Slk;

final class FileReader implements FileReaderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getData(string $filename, array $options = []): array
    {
        $reader = IOFactory::createReaderForFile($filename);

        $encoding = $options['encoding'] ?? null;
        if (($reader instanceof Csv || $reader instanceof Html || $reader instanceof Slk) && null !== $encoding) {
            $reader->setInputEncoding($encoding);
        }

        if ($reader instanceof Csv && isset($options['delimiter'])) {
            $reader->setDelimiter($options['delimiter']);
        }

        $data = $reader->load($filename)->getActiveSheet()->toArray();

        $excludeRows = ($options['exclude_rows'] ?? []);
        if (\count($excludeRows)) {
            $data = $this->excludeRows($data, ...$excludeRows);
        }

        return $data;
    }

    /**
     * @param array<int, array<mixed>> $data
     *
     * @return array<int, array<mixed>>
     */
    private function excludeRows(array $data, int ...$positions): array
    {
        $indexesToRemove = \array_map(static fn (int $i) => $i < 0 ? \count($data) + $i : $i, $positions);
        $removeCallback = static fn ($key) => !\in_array($key, $indexesToRemove, true);

        return \array_values(\array_filter($data, $removeCallback, ARRAY_FILTER_USE_KEY));
    }
}
