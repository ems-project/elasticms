<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Asset;

final readonly class AssetInfoVariant
{
    public function __construct(
        private string $name,
        private string $mimeType,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }
}
