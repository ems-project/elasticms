<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Filter\Html;

use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Config\WebResource;
use Symfony\Component\DomCrawler\Crawler;

class TagCleaner implements HtmlInterface
{
    final public const string TYPE = 'tag-cleaner';

    public function __construct(private readonly ConfigManager $config)
    {
    }

    #[\Override]
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
