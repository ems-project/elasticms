<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary\File;

use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\CoreBundle\Core\Component\MediaLibrary\Config\MediaLibraryConfig;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaLibraryFile
{
    /** @var array{filename: string, sha1: string, filesize: int, mimetype: string} */
    public array $file;

    public string $path;
    public string $folder;
    public string $id;
    public string $emsId;

    public function __construct(
        private readonly MediaLibraryConfig $config,
        private readonly UrlGeneratorInterface $urlGenerator,
        public DocumentInterface $document
    ) {
        $this->id = $this->document->getId();
        $this->emsId = (string) $document->getEmsLink();

        $this->file = $document->getValue($config->fieldFile);
        $this->path = $document->getValue($config->fieldPath);
        $this->folder = $document->getValue($config->fieldFolder);
    }

    public function urlView(): string
    {
        return $this->urlGenerator->generate('ems.file.view', [
            'sha1' => $this->file['sha1'],
            'type' => $this->file['mimetype'],
            'name' => $this->getName(),
        ]);
    }

    public function setName(string $name): void
    {
        $path = \pathinfo($this->path, PATHINFO_DIRNAME).'/'.$name;
        $this->path = $path;
        $this->document->setValue($this->config->fieldPath, $path);
    }

    public function getName(): string
    {
        return \basename($this->path);
    }
}
