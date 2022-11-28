<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Extract;

use App\CLI\Client\WebToElasticms\Config\Document;

class ExtractedData
{
    private Document $document;
    /**
     * @var mixed[]
     */
    private array $data;

    /**
     * @param array<mixed> $data
     */
    public function __construct(Document $document, array $data)
    {
        $this->document = $document;
        $this->data = $data;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }
}
