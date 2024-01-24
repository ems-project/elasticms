<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary\Folder;

use EMS\CommonBundle\Common\PropertyAccess\PropertyAccessor;
use EMS\CommonBundle\Common\PropertyAccess\PropertyPath;
use EMS\CommonBundle\Elasticsearch\Document\DocumentCollectionInterface;
use EMS\CoreBundle\Core\Component\MediaLibrary\Config\MediaLibraryConfig;
use EMS\CoreBundle\Core\Component\MediaLibrary\MediaLibraryPath;

class MediaLibraryFolders
{
    /** @var array<MediaLibraryFolder> */
    public array $folders = [];

    private PropertyAccessor $propertyAccessor;

    public function __construct(private readonly MediaLibraryConfig $config)
    {
        $this->propertyAccessor = PropertyAccessor::createPropertyAccessor();
    }

    public function addDocuments(DocumentCollectionInterface $collection): void
    {
        foreach ($collection as $document) {
            $folder = new MediaLibraryFolder($document, $this->config);
            $this->folders[$folder->path->getValue()] = $folder;
        }
    }

    /**
     * @return array<string, array{ id: string, name: string, path: string, children: array<string, mixed> }>
     */
    public function getStructure(): array
    {
        $structure = [];

        foreach ($this->getFolders() as $folder) {
            if (0 === \count($folder->path)) {
                continue;
            }

            $parentProperty = $folder->path->parent() ? $this->createStructurePath($folder->path->parent()) : null;
            if ($parentProperty && null === $this->propertyAccessor->getValue($structure, $parentProperty)) {
                continue;
            }

            $folderProperty = $this->createStructurePath($folder->path);
            $this->propertyAccessor->setValue($structure, $folderProperty, [
                'id' => $folder->id,
                'name' => $folder->getName(),
                'path' => $folder->path,
            ]);
        }

        return $structure;
    }

    /**
     * @return MediaLibraryFolder[]
     */
    private function getFolders(): array
    {
        $folders = $this->folders;
        \ksort($folders);

        return $folders;
    }

    private function createStructurePath(MediaLibraryPath $path): PropertyPath
    {
        return new PropertyPath(\sprintf('[%s]', \implode('][children][', $path->value)));
    }
}
