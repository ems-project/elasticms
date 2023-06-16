<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

class MediaLibraryFolder
{
    public function __construct(
        public string $id,
        public string $name,
        public string $path
    ) {
    }
}
