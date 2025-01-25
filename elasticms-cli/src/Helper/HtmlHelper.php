<?php

declare(strict_types=1);

namespace App\CLI\Helper;

use App\CLI\Client\Audit\Report;
use EMS\CommonBundle\Helper\Url;
use Symfony\Component\DomCrawler\Crawler;

class HtmlHelper
{
    private readonly Crawler $crawler;

    public function __construct(string $content, private readonly Url $referer)
    {
        $this->crawler = new Crawler($content);
    }

    /**
     * @return array<string, string>
     */
    public function getLinks(): iterable
    {
        $content = $this->crawler->filter('a');
        for ($i = 0; $i < $content->count(); ++$i) {
            $item = $content->eq($i);
            $href = $item->attr('href');
            if (null === $href || 0 === \strlen($href) || \str_starts_with($href, '#')) {
                continue;
            }
            yield $href => \html_entity_decode($item->text());
        }
    }

    public function getText(): string
    {
        $body = $this->crawler->filter('body');

        return $body->text();
    }

    public function getUniqueTextValue(Report $report, string $selector): ?string
    {
        $tag = $this->crawler->filter($selector);
        if (0 === $tag->count() || 0 === \strlen(\trim($tag->eq(0)->text()))) {
            $report->addWarning($this->referer, [\sprintf('%s is missing', $selector)]);

            return null;
        }
        if ($tag->count() > 1) {
            $report->addWarning($this->referer, [\sprintf('%s is present %d times', $selector, $tag->count())]);
        }

        return \trim($tag->eq(0)->text());
    }

    public function getUniqueTextAttr(Report $report, string $selector, string $attr, bool $withWarnings = true): ?string
    {
        $tag = $this->crawler->filter($selector);
        if (0 === $tag->count() || 0 === \strlen(\trim($tag->eq(0)->attr($attr) ?? ''))) {
            if ($withWarnings) {
                $report->addWarning($this->referer, [\sprintf('%s is missing', $selector)]);
            }

            return null;
        }
        if ($tag->count() > 1) {
            $report->addWarning($this->referer, [\sprintf('%s is present %d times', $selector, $tag->count())]);
        }

        return \trim($tag->eq(0)->attr($attr) ?? '');
    }

    public function getReferer(): Url
    {
        return $this->referer;
    }
}
