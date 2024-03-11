<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

use EMS\CoreBundle\Core\Component\MediaLibrary\Folder\MediaLibraryFolder;
use EMS\CoreBundle\Validator\Constraints as EMSAssert;
use Symfony\Component\Validator\Constraints as Assert;

#[EMSAssert\MediaLibrary\DocumentDTO]
class MediaLibraryDocumentDTO
{
    #[Assert\NotBlank]
    public ?string $name = null;

    private function __construct(
        private readonly string $folder
    ) {
    }

    public static function newFolder(?MediaLibraryFolder $parentFolder = null): self
    {
        $folder = $parentFolder ? $parentFolder->getPath()->getValue().'/' : '/';

        return new self($folder);
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
