<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\File;

interface FileReaderInterface
{
    /**
     * @param array{
     *     delimiter?: ?string,
     *     encoding?: ?string,
     * } $options
     *
     * @return array<int, array<mixed>>
     */
    public function getData(string $filename, array $options = []): array;

    /**
     * @param array{
     *      delimiter?: ?string,
     *      encoding?: ?string,
     *      exclude_rows?: int[],
     *      limit?: ?int,
     *  } $options
     *
     * @return \Generator<mixed>
     */
    public function readCells(string $filename, array $options = []): \Generator;
}
