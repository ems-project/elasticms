<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

class MediaLibraryFolders
{
    /** @var MediaLibraryFolder[] */
    public array $folders = [];

    /**
     * @param string[] $currentPath
     */
    public function add(array $currentPath, string $folderId, string $folderName, string $folderPath): void
    {
        $childName = \array_shift($currentPath);

        if ($childName && !isset($this->folders[$childName])) {
            $this->folders[$childName] = new MediaLibraryFolder($folderId, $childName, $folderPath);
        }

        if (\count($currentPath) > 0) {
            $this->folders[$childName]->folders->add($currentPath, $folderId, $folderName, $folderPath);
        }
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->folders as $folder) {
            $result[] = \array_filter([
                'id' => $folder->id,
                'name' => $folder->name,
                'path' => $folder->path,
                'children' => $folder->folders->toArray(),
            ]);
        }

        return $result;
    }
}
