<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Filter\Html;

use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Config\WebResource;
use App\CLI\Client\WebToElasticms\Rapport\Rapport;
use Symfony\Component\DomCrawler\Crawler;

class DataLink
{
    public const TYPE = 'data-link';
    private ConfigManager $config;
    private Rapport $rapport;

    public function __construct(ConfigManager $config, Rapport $rapport)
    {
        $this->config = $config;
        $this->rapport = $rapport;
    }

    public function process(WebResource $resource, Crawler $content, string $type): void
    {
        if (null !== $content->getNode(0)) {
            $path = $this->config->findDataLink($content->getNode(0)->textContent, $this->rapport, $resource->getUrl(), $type);
            if (null !== $path) {
                $content->getNode(0)->nodeValue = $path;
            }
        }
    }
}
