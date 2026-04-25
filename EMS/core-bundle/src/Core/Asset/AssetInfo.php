<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Asset;

final readonly class AssetInfo
{
    /**
     * @param AssetInfoVariant[] $alternatives
     */
    public function __construct(
        private string $firstName,
        private string $firstMimeType,
        private int $size,
        private \DateTimeInterface $firstUploadedAt,
        private array $alternatives,
    ) {
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getFirstMimeType(): string
    {
        return $this->firstMimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getFirstUploadedAt(): \DateTimeInterface
    {
        return $this->firstUploadedAt;
    }

    /**
     * @return AssetInfoVariant[]
     */
    public function getAlternatives(): array
    {
        return $this->alternatives;
    }
}
