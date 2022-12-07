<?php

namespace EMS\CommonBundle\Elasticsearch;

/**
 * @deprecated use EMS\CommonBundle\Elasticsearch\Document\Document
 */
class Document implements DocumentInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array<mixed>
     */
    private readonly array $source;

    /**
     * @param array<mixed> $document
     */
    public function __construct(array $document)
    {
        $this->id = $document['_id'];
        $this->type = $document['_type'];
        $this->source = $document['_source'] ?? [];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEmsId(): string
    {
        return "$this->type:$this->id";
    }

    /**
     * @return array<mixed>
     */
    public function getSource(): array
    {
        return $this->source;
    }
}
