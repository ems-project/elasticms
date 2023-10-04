<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary\File;

use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\CoreBundle\Core\Component\MediaLibrary\Config\MediaLibraryConfig;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaLibraryFileFactory
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MediaLibraryConfig $config
    ) {
    }

    public function createFromDocument(DocumentInterface $document): MediaLibraryFile
    {
        return new MediaLibraryFile($this->config, $this->urlGenerator, $document);
    }
}
