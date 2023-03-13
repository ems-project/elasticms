<?php

declare(strict_types=1);

namespace App\CLI\Client\MediaLibrary;

final class MediaLibraryMap
{
    public string $field;
    public int $indexDataColumn;
    public bool $isFolder;
    public bool $isFilename;
    public bool $isRequired;

    /**
     * @param array{'field': string, 'indexDataColumn': int, 'isFolder': ?bool, 'isFilename': ?bool, 'isRequired': ?bool } $config
     */
    public function __construct(array $config)
    {
        $this->field = $config['field'];
        $this->indexDataColumn = $config['indexDataColumn'];
        $this->isFolder = $config['isFolder'] ?? false;
        $this->isFilename = $config['isFilename'] ?? false;
        $this->isRequired = $config['isRequired'] ?? true;
    }
}
