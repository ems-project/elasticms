<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Filter\Html;

use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Config\WebResource;
use Symfony\Component\DomCrawler\Crawler;

class StyleCleaner implements HtmlInterface
{
    final public const string TYPE = 'style-cleaner';

    public function __construct(private readonly ConfigManager $config)
    {
    }

    #[\Override]
    public function process(WebResource $resource, Crawler $content): void
    {
        foreach ($content->filter('[style]') as $item) {
            if (!$item instanceof \DOMElement) {
                throw new \RuntimeException('Unexpected non DOMElement object');
            }

            if (\in_array($item->nodeName, $this->config->getStyleValidTags())) {
                continue;
            }

            $item->removeAttribute('style');
        }
    }
}
