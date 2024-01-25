<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\CoreBundle\Core\Component\MediaLibrary\Config\MediaLibraryConfig;

class MediaLibraryDocument
{
    public string $id;
    public string $emsId;
    public MediaLibraryPath $folder;
    public MediaLibraryPath $path;

    public function __construct(
        public DocumentInterface $document,
        private readonly MediaLibraryConfig $config,
    ) {
        $this->id = $this->document->getId();
        $this->emsId = (string) $document->getEmsLink();

        $this->path = MediaLibraryPath::fromString($document->getValue($config->fieldPath));
        $this->folder = MediaLibraryPath::fromString($document->getValue($config->fieldFolder));
    }

    public function getPath(): string
    {
        return $this->path->getValue();
    }

    public function getName(): string
    {
        return $this->path->getName();
    }

    public function setName(string $name): void
    {
        $this->path = $this->path->setName($name);
        $this->document->setValue($this->config->fieldPath, $this->path->getValue());
    }
}
