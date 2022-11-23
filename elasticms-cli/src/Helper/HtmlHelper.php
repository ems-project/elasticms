<?php

namespace App\Helper;

use Symfony\Component\DomCrawler\Crawler;

class HtmlHelper
{
    private Crawler $crawler;

    public function __construct(string $content)
    {
        $this->crawler = new Crawler($content);
    }

    /**
     * @return string[]
     */
    public function getLinks(): array
    {
        $content = $this->crawler->filter('a');
        $externalLinks = [];
        for ($i = 0; $i < $content->count(); ++$i) {
            $item = $content->eq($i);
            $href = $item->attr('href');
            if (null === $href || 0 === \strlen($href) || '#' === \substr($href, 0, 1)) {
                continue;
            }
            $externalLinks[] = $href;
        }

        return $externalLinks;
    }

    public function getText(): string
    {
        $body = $this->crawler->filter('body');

        return $body->text();
    }
}
