<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

use EMS\CoreBundle\Core\Component\MediaLibrary\Folder\MediaLibraryFolder;
use EMS\CoreBundle\Validator\Constraints as EMSAssert;
use Symfony\Component\Validator\Constraints as Assert;

#[EMSAssert\MediaLibrary\DocumentDTO]
class MediaLibraryDocumentDTO
{
    public const TYPE_FOLDER = 'folder';
    public const TYPE_FILE = 'file';

    private function __construct(
        private readonly string $folder,
        public readonly string $type,
        #[Assert\NotBlank]
        public ?string $name = null,
        public ?string $id = null,
        public ?int $filesize = null,
        public ?string $mimetype = null,
        public ?string $fileHash = null
    ) {
    }

    public static function createFile(?MediaLibraryFolder $parentFolder = null): self
    {
        $folder = $parentFolder ? $parentFolder->getPath()->getValue().'/' : '/';

        return new self(folder: $folder, type: self::TYPE_FILE);
    }

    public static function createFolder(?MediaLibraryFolder $parentFolder = null): self
    {
        $folder = $parentFolder ? $parentFolder->getPath()->getValue().'/' : '/';

        return new self($folder, self::TYPE_FOLDER);
    }

    public static function updateFolder(MediaLibraryFolder $folder): self
    {
        $dto = new self($folder->getPath()->getFolderValue(), self::TYPE_FOLDER);
        $dto->id = $folder->id;
        $dto->name = $folder->getName();

        return $dto;
    }

    public function getFolder(): string
    {
        return $this->folder;
    }

    public function getPath(): string
    {
        return $this->getFolder().$this->name;
    }
}
