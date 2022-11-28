<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Filter\Html;

use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Config\WebResource;
use Symfony\Component\DomCrawler\Crawler;

class TagCleaner implements HtmlInterface
{
    public const TYPE = 'tag-cleaner';
    private ConfigManager $config;

    public function __construct(ConfigManager $config)
    {
        $this->config = $config;
    }

    public function process(WebResource $resource, Crawler $content): void
    {
        foreach ($content->filter(\implode(', ', $this->config->getCleanTags())) as $item) {
            if (!$item instanceof \DOMElement) {
                continue;
            }

            if ($item->parentNode instanceof \DOMElement) {
                $item->parentNode->removeChild($item);
            }
        }
    }
}
