<?php

declare(strict_types=1);

namespace App\Client\WebToElasticms\Extract;

use App\Client\HttpClient\HttpResult;
use App\Client\WebToElasticms\Config\Analyzer;
use App\Client\WebToElasticms\Config\ConfigManager;
use App\Client\WebToElasticms\Config\Document;
use App\Client\WebToElasticms\Config\WebResource;
use App\Client\WebToElasticms\Rapport\Rapport;
use Psr\Log\LoggerInterface;

class EmptyExtractor
{
    public const TYPE = 'empty-extractor';
    private ConfigManager $config;
    private Document $document;
    private LoggerInterface $logger;
    private Rapport $rapport;

    /**
     * @param array<mixed> $data
     */
    public function extractData(WebResource $resource, HttpResult $result, Analyzer $analyzer, array &$data): void
    {
        // No data to extract
    }
}
