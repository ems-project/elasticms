<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Filter\Html;

use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Config\WebResource;
use Symfony\Component\DomCrawler\Crawler;

class ClassCleaner implements HtmlInterface
{
    final public const string TYPE = 'class-cleaner';

    public function __construct(private readonly ConfigManager $config)
    {
    }

    #[\Override]
    public function process(WebResource $resource, Crawler $content): void
    {
        foreach ($content->filter('[class]') as $item) {
            if (!$item instanceof \DOMElement) {
                throw new \RuntimeException('Unexpected non DOMElement object');
            }
            $classes = \explode(' ', $item->getAttribute('class'));
            $classes = \array_intersect($classes, $this->config->getValidClasses());
            if (empty($classes)) {
                $item->removeAttribute('class');
            } else {
                $item->setAttribute('class', \implode(' ', $classes));
            }
        }
    }
}
