<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\File;

use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class FileReader implements FileReaderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getData(string $filename, bool $skipFirstRow = false): array
    {
        $reader = IOFactory::createReaderForFile($filename);

        $data = $reader->load($filename)->getActiveSheet()->toArray();

        if ($skipFirstRow) {
            unset($data[0]);
        }

        return $data;
    }
}
