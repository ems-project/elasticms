<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Filter\Html;

use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Config\WebResource;
use App\CLI\Client\WebToElasticms\Rapport\Rapport;
use Symfony\Component\DomCrawler\Crawler;

class DataLink
{
    final public const string TYPE = 'data-link';

    public function __construct(private readonly ConfigManager $config, private readonly Rapport $rapport)
    {
    }

    public function process(WebResource $resource, Crawler $content, string $type): void
    {
        if (null !== $content->getNode(0)) {
            $path = $this->config->findDataLink($content->getNode(0)->textContent, $this->rapport, $resource->getUrl(), $type);
            $content->getNode(0)->nodeValue = $path;
        }
    }
}
