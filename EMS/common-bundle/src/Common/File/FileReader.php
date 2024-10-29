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
        $skipFirstRow = true === ($options['skipFirstRow'] ?? false);
        $encoding = $options['encoding'] ?? null;

        $reader = IOFactory::createReaderForFile($filename);
        if (($reader instanceof Csv || $reader instanceof Html || $reader instanceof Slk) && null !== $encoding) {
            $reader->setInputEncoding($encoding);
        }

        if ($reader instanceof Csv && isset($options['delimiter'])) {
            $reader->setDelimiter($options['delimiter']);
        }

        $data = $reader->load($filename)->getActiveSheet()->toArray();

        if ($skipFirstRow) {
            unset($data[0]);
        }

        return $data;
    }
}
