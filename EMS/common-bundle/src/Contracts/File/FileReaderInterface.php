<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\File;

interface FileReaderInterface
{
    /**
     * @param array{
     *     skipFirstRow?: bool,
     *     encoding?: ?string,
     *     delimiter?: ?string,
     * } $options
     *
     * @return array<mixed>
     */
    public function getData(string $filename, array $options = []): array;
}
