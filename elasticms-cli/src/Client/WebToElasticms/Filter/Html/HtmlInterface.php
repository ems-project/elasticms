<?php

declare(strict_types=1);

namespace App\Client\WebToElasticms\Filter\Html;

use App\Client\WebToElasticms\Config\WebResource;
use Symfony\Component\DomCrawler\Crawler;

interface HtmlInterface
{
    public function process(WebResource $resource, Crawler $content): void;
}
