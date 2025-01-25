<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Document;

use EMS\CommonBundle\Common\EMSLink;

interface DocumentInterface
{
    public function getId(): string;

    public function getDocumentEmsId(): string;

    public function getOuuid(): string;

    public function getContentType(): string;

    public function getEmsId(): string;

    public function getEmsLink(): EMSLink;

    /**
     * @return array<mixed>
     */
    public function getSource(bool $cleaned = false): array;

    public function getValue(string $fieldPath, mixed $defaultValue = null): mixed;

    public function setValue(string $fieldPath, mixed $value): self;

    public function getEMSSource(): EMSSourceInterface;
}
