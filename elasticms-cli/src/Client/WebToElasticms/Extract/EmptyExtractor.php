<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Extract;

use App\CLI\Client\HttpClient\HttpResult;
use App\CLI\Client\WebToElasticms\Config\Analyzer;
use App\CLI\Client\WebToElasticms\Config\WebResource;

class EmptyExtractor
{
    final public const string TYPE = 'empty-extractor';

    /**
     * @param array<mixed> $data
     */
    public function extractData(WebResource $resource, HttpResult $result, Analyzer $analyzer, array &$data): void
    {
        // No data to extract
    }
}
