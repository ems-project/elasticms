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
    public function getData(string $filename, bool $skipFirstRow = false, string $encoding = null): array
    {
        $reader = IOFactory::createReaderForFile($filename);
        if (($reader instanceof Csv || $reader instanceof Html || $reader instanceof Slk) && null !== $encoding) {
            $reader->setInputEncoding($encoding);
        }

        $data = $reader->load($filename)->getActiveSheet()->toArray();

        if ($skipFirstRow) {
            unset($data[0]);
        }

        return $data;
    }
}
