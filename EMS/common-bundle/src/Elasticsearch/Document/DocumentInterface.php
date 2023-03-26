<?php

namespace EMS\CommonBundle\Elasticsearch\Document;

use EMS\CommonBundle\Common\EMSLink;

interface DocumentInterface
{
    public function getId(): string;

    public function getOuuid(): string;

    public function getContentType(): string;

    public function getEmsId(): string;

    public function getEmsLink(): EMSLink;

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
