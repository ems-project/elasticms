<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Document;

interface EMSSourceInterface
{
    /**
     * @return mixed
     */
    public function get(string $field, mixed $default = null);

    public function getContentType(): string;

    public function getHash(): string;

    public function hasFinalizedBy(): bool;

    public function hasFinalizationDateTime(): bool;

    public function hasPublicationDateTime(): bool;

    public function getFinalizedBy(): string;

    public function getFinalizationDateTime(): \DateTimeInterface;

    public function getPublicationDateTime(): \DateTimeInterface;

    /**
     * @return array<mixed>
     */
    public function toArray(): array;
}
