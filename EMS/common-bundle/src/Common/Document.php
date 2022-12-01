<?php

namespace EMS\CommonBundle\Common;

class Document
{
    /**
     * @param array<mixed> $source
     */
    public function __construct(private readonly string $contentType, private readonly string $ouuid, private readonly array $source)
    {
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getOuuid(): string
    {
        return $this->ouuid;
    }

    /**
     * @return array<mixed>
     */
    public function getSource(): array
    {
        return $this->source;
    }

    public function getEmsLink(): EMSLink
    {
        return EMSLink::fromContentTypeOuuid($this->contentType, $this->ouuid);
    }
}
