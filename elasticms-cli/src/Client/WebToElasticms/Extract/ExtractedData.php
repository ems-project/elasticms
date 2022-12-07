<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Extract;

use App\CLI\Client\WebToElasticms\Config\Document;

class ExtractedData
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(private readonly Document $document, private readonly array $data)
    {
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
