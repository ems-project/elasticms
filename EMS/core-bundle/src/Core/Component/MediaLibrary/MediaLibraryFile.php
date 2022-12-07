<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

use Elastica\Document;

class MediaLibraryFile
{
    /**
     * @param ?array{filename: string, filesize: int, mimetype: string, sha1: string } $file
     */
    private function __construct(
        private readonly string $path,
        private readonly ?array $file
    ) {
    }

    public static function createFromDocument(MediaLibraryConfig $config, Document $document): self
    {
        return new self(
            (string) $document->get($config->fieldPath),
            $document->has($config->fieldFile) ? $document->get($config->fieldFile) : null
        );
    }

    /**
     * @return array{ path: string, file?: array{filename: string, filesize: int, mimetype: string, sha1: string } }
     */
    public function toArray(): array
    {
        $file = ['path' => $this->path];

        if ($this->file) {
            $file['file'] = $this->file;
        }

        return $file;
    }
}
