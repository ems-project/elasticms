<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\File;

interface FileReaderInterface
{
    /**
     * @param array{
     *     encoding?: ?string,
     *     exclude_rows?: int[],
     *     delimiter?: ?string,
     * } $options
     *
     * @return array<int, array<mixed>>
     */
    public function getData(string $filename, array $options = []): array;
}
