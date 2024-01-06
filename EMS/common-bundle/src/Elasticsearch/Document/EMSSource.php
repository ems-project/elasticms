<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Document;

use EMS\Helpers\Standard\DateTime;

final class EMSSource implements EMSSourceInterface
{
    private readonly string $contentType;
    private readonly string $hash;
    private readonly ?string $finalizedBy;
    private ?\DateTimeInterface $finalizationDateTime = null;
    private ?\DateTimeInterface $publicationDateTime = null;
    /** @var array<mixed> */
    private readonly array $source;

    public const FIELD_CONTENT_TYPE = '_contenttype';
    public const FIELD_FINALIZED_BY = '_finalized_by';
    public const FIELD_FINALIZATION_DATETIME = '_finalization_datetime';
    public const FIELD_HASH = '_sha1';
    public const FIELD_SIGNATURE = '_signature';
    public const FIELD_PUBLICATION_DATETIME = '_published_datetime';
    public const FIELD_VERSION_UUID = '_version_uuid';
    public const FIELD_VERSION_TAG = '_version_tag';

    public const REQUIRED_FIELDS = [
        EMSSource::FIELD_CONTENT_TYPE,
        EMSSource::FIELD_VERSION_UUID,
        EMSSource::FIELD_HASH,
    ];

    /**
     * @param array<mixed> $source
     */
    public function __construct(array $source)
    {
        $this->contentType = $source[self::FIELD_CONTENT_TYPE];
        $this->finalizedBy = $source[self::FIELD_FINALIZED_BY] ?? null;
        $this->hash = $source[self::FIELD_HASH] ?? 'hash-not-available';
        $this->source = $source;

        if (isset($source[self::FIELD_FINALIZATION_DATETIME])) {
            $this->finalizationDateTime = DateTime::createFromFormat($source[self::FIELD_FINALIZATION_DATETIME]);
        }
        if (isset($source[self::FIELD_PUBLICATION_DATETIME])) {
            $this->publicationDateTime = DateTime::createFromFormat($source[self::FIELD_PUBLICATION_DATETIME]);
        }
    }

    public function get(string $field, $default = null)
    {
        return $this->source[$field] ?? $default;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function hasFinalizedBy(): bool
    {
        return null !== $this->finalizedBy;
    }

    public function getFinalizedBy(): string
    {
        if (null === $finalizedBy = $this->finalizedBy) {
            throw new \RuntimeException('Finalized by missing');
        }

        return $finalizedBy;
    }

    public function hasFinalizationDateTime(): bool
    {
        return null !== $this->finalizationDateTime;
    }

    public function getFinalizationDateTime(): \DateTimeInterface
    {
        if (null === $finalizationDateTime = $this->finalizationDateTime) {
            throw new \RuntimeException('Finalization datetime by missing');
        }

        return $finalizationDateTime;
    }

    public function hasPublicationDateTime(): bool
    {
        return null !== $this->publicationDateTime;
    }

    public function getPublicationDateTime(): \DateTimeInterface
    {
        if (null === $publicationDateTime = $this->publicationDateTime) {
            throw new \RuntimeException('Finalization datetime by missing');
        }

        return $publicationDateTime;
    }

    public function toArray(): array
    {
        return $this->source;
    }
}
