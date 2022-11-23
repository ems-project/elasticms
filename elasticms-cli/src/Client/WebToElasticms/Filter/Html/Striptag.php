<?php

declare(strict_types=1);

namespace App\Client\WebToElasticms\Filter\Html;

use App\Client\WebToElasticms\Config\ConfigManager;
use App\Client\WebToElasticms\Config\WebResource;
use Symfony\Component\DomCrawler\Crawler;

class Striptag implements HtmlInterface
{
    public const TYPE = 'striptags';
    private ConfigManager $config;

    public function __construct(ConfigManager $config)
    {
        $this->config = $config;
    }

    public function process(WebResource $resource, Crawler $content): void
    {
    }
}
