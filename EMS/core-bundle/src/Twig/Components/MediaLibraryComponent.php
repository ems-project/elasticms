<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Twig\Components;

use EMS\CoreBundle\Core\Component\MediaLibrary\MediaLibraryConfigFactory;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PreMount;

final class MediaLibraryComponent
{
    public function __construct(
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
        $this->hash = $this->mediaLibraryConfigFactory->create($options)->getHash();

        return $options;
    }
}
