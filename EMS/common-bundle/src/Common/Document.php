<?php

namespace EMS\CommonBundle\Common;

class Document
{
    private string $contentType;
    private string $ouuid;
    /** @var array<mixed> */
    private array $source;

    /**
     * @param array<mixed> $source
     */
    public function __construct(string $contentType, string $ouuid, array $source)
    {
        $this->contentType = $contentType;
        $this->ouuid = $ouuid;
        $this->source = $source;
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
