<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\CoreBundle\Core\Component\MediaLibrary\Config\MediaLibraryConfig;

class MediaLibraryDocument
{
    public string $path;
    public string $folder;
    public string $id;
    public string $emsId;

    public function __construct(
        public DocumentInterface $document,
        private readonly MediaLibraryConfig $config,
    ) {
        $this->id = $this->document->getId();
        $this->emsId = (string) $document->getEmsLink();

        $this->path = $document->getValue($config->fieldPath);
        $this->folder = $document->getValue($config->fieldFolder);
    }

    public function getPath(): string
    {
        return $this->path.'/';
    }

    public function getName(): string
    {
        return \basename($this->path);
    }

    public function setName(string $name): void
    {
        $path = \pathinfo($this->path, PATHINFO_DIRNAME).'/'.$name;
        $this->path = $path;
        $this->document->setValue($this->config->fieldPath, $path);
    }
}
