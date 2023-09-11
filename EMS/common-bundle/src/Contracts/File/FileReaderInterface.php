<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\File;

interface FileReaderInterface
{
    /**
     * @return array<mixed>
     */
    public function getData(string $filename, bool $skipFirstRow = false, string $encoding = null): array;
}
