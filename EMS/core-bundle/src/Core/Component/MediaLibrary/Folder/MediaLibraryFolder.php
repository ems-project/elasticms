<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary\Folder;

use EMS\CoreBundle\Core\Component\MediaLibrary\MediaLibraryDocument;

class MediaLibraryFolder extends MediaLibraryDocument
{
    private ?MediaLibraryFolder $parent = null;

    /**
     * @return MediaLibraryFolder[]
     */
    public function parents(): array
    {
        $parents = [$this];

        if ($this->parent) {
            $parents = \array_merge($this->parent->parents(), $parents);
        }

        return $parents;
    }

    public function getParentPath(): ?string
    {
        $path = \array_filter(\explode('/', $this->path));
        \array_pop($path);

        return $path ? '/'.\implode('/', $path) : null;
    }

    public function getParent(): ?MediaLibraryFolder
    {
        return $this->parent;
    }

    public function setParent(MediaLibraryFolder $parent): void
    {
        $this->parent = $parent;
    }
}
