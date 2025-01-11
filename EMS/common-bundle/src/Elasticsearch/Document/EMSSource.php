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

    public const string FIELD_CONTENT_TYPE = '_contenttype';
    public const string FIELD_FINALIZED_BY = '_finalized_by';
    public const string FIELD_FINALIZATION_DATETIME = '_finalization_datetime';
    public const string FIELD_HASH = '_sha1';
    public const string FIELD_SIGNATURE = '_signature';
    public const string FIELD_PUBLICATION_DATETIME = '_published_datetime';
    public const string FIELD_VERSION_UUID = '_version_uuid';
    public const string FIELD_VERSION_TAG = '_version_tag';

    public const array REQUIRED_FIELDS = [
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

    #[\Override]
    public function get(string $field, mixed $default = null)
    {
        return $this->source[$field] ?? $default;
    }

    #[\Override]
    public function getContentType(): string
    {
        return $this->contentType;
    }

    #[\Override]
    public function getHash(): string
    {
        return $this->hash;
    }

    #[\Override]
    public function hasFinalizedBy(): bool
    {
        return null !== $this->finalizedBy;
    }

    #[\Override]
    public function getFinalizedBy(): string
    {
        if (null === $finalizedBy = $this->finalizedBy) {
            throw new \RuntimeException('Finalized by missing');
        }

        return $finalizedBy;
    }

    #[\Override]
    public function hasFinalizationDateTime(): bool
    {
        return null !== $this->finalizationDateTime;
    }

    #[\Override]
    public function getFinalizationDateTime(): \DateTimeInterface
    {
        if (null === $finalizationDateTime = $this->finalizationDateTime) {
            throw new \RuntimeException('Finalization datetime by missing');
        }

        return $finalizationDateTime;
    }

    #[\Override]
    public function hasPublicationDateTime(): bool
    {
        return null !== $this->publicationDateTime;
    }

    #[\Override]
    public function getPublicationDateTime(): \DateTimeInterface
    {
        if (null === $publicationDateTime = $this->publicationDateTime) {
            throw new \RuntimeException('Finalization datetime by missing');
        }

        return $publicationDateTime;
    }

    #[\Override]
    public function toArray(): array
    {
        return $this->source;
    }
}
