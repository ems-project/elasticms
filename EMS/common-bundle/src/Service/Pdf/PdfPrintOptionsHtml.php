<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Service\Pdf;

use Symfony\Component\DomCrawler\Crawler;

final class PdfPrintOptionsHtml extends PdfPrintOptions
{
    public function __construct(string $html)
    {
        parent::__construct($this->getOptionsFromHtml($html));
    }

    /**
     * @return array<string, mixed>
     */
    private function getOptionsFromHtml(string $html): array
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($html);

        $metaTags = [];

        foreach ($crawler->filterXPath('//meta[contains(@name, "pdf:")]') as $metaTag) {
            if (!$metaTag instanceof \DOMElement) {
                continue;
            }

            $name = \substr($metaTag->getAttribute('name'), 4);
            $metaTags[$name] = $metaTag->getAttribute('content');
        }

        return $this->sanitizeMetaTags($metaTags);
    }

    /**
     * @param array<mixed> $metaData
     *
     * @return array<mixed>
     */
    private function sanitizeMetaTags(array $metaData): array
    {
        $filtered = \filter_var_array($metaData, [
            self::FILENAME => null,
            self::ATTACHMENT => FILTER_VALIDATE_BOOLEAN,
            self::COMPRESS => FILTER_VALIDATE_BOOLEAN,
            self::HTML5_PARSING => FILTER_VALIDATE_BOOLEAN,
            self::PHP_ENABLED => FILTER_VALIDATE_BOOLEAN,
            self::ORIENTATION => null,
            self::SIZE => null,
        ], false);

        if (!\is_array($filtered)) {
            throw new \RuntimeException('Unexpected sanitizeMetaTags error');
        }

        return $filtered;
    }
}
