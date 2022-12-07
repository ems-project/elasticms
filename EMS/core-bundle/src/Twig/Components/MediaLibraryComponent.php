<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Twig\Components;

use EMS\CommonBundle\Storage\StorageManager;
use EMS\CoreBundle\Core\Component\MediaLibrary\MediaLibraryConfigFactory;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PreMount;

final class MediaLibraryComponent
{
    public function __construct(
        private readonly StorageManager $storageManager,
        private readonly MediaLibraryConfigFactory $mediaLibraryConfigFactory
    ) {
    }

    #[ExposeInTemplate('hash')]
    public string $hash;

    /**
     * @param array<mixed> $options
     *
     * @return array<mixed>
     */
    #[PreMount]
    public function validate(array $options): array
    {
        $config = $this->mediaLibraryConfigFactory->create($options);

        $this->hash = $this->storageManager->saveConfig($config->options);

        return $config->options;
    }
}
