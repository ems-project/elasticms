<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Twig\Components;

use EMS\CommonBundle\Storage\StorageManager;
use EMS\CoreBundle\Core\Component\MediaLibrary\MediaLibraryConfig;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PreMount;

final class MediaLibraryComponent
{
    public function __construct(protected readonly StorageManager $storageManager)
    {
    }

    #[ExposeInTemplate('hash')]
    public string $hash;

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    #[PreMount]
    public function validate(array $data): array
    {
        $config = new MediaLibraryConfig($data);

        $this->hash = $this->storageManager->saveConfig($config->options);

        return $config->options;
    }
}
