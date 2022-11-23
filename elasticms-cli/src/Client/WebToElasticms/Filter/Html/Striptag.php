<?php

declare(strict_types=1);

namespace App\Client\WebToElasticms\Filter\Html;

use App\Client\WebToElasticms\Config\WebResource;
use Symfony\Component\DomCrawler\Crawler;

class Striptag implements HtmlInterface
{
    public const TYPE = 'striptags';

    public function process(WebResource $resource, Crawler $content): void
    {
    }
}
