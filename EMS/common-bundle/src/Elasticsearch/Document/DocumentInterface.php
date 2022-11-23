<?php

namespace EMS\CommonBundle\Elasticsearch\Document;

interface DocumentInterface
{
    public function getId(): string;

    public function getContentType(): string;

    public function getEmsId(): string;

    /**
     * @return array<mixed>
     */
    public function getSource(bool $cleaned = false): array;

    /**
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public function getValue(string $fieldPath, $defaultValue = null);

    public function getEMSSource(): EMSSourceInterface;
}
