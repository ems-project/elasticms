<?php

declare(strict_types=1);

namespace App\Client\WebToElasticms\Filter\Html;

use App\Client\WebToElasticms\Config\WebResource;
use Symfony\Component\DomCrawler\Crawler;

class StyleCleaner implements HtmlInterface
{
    public const TYPE = 'style-cleaner';

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
