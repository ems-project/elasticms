<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Filter\Html;

use App\CLI\Client\WebToElasticms\Config\WebResource;
use Symfony\Component\DomCrawler\Crawler;

class StyleCleaner implements HtmlInterface
{
    final public const TYPE = 'style-cleaner';

    public function process(WebResource $resource, Crawler $content): void
    {
        foreach ($content->filter('[style]') as $item) {
            if (!$item instanceof \DOMElement) {
                throw new \RuntimeException('Unexpected non DOMElement object');
            }
            $item->removeAttribute('style');
        }
    }
}
