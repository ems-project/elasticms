<?php

declare(strict_types=1);

namespace App\Client\WebToElasticms\Extract;

use App\Client\HttpClient\HttpResult;
use App\Client\WebToElasticms\Config\Analyzer;
use App\Client\WebToElasticms\Config\ConfigManager;
use App\Client\WebToElasticms\Config\Document;
use App\Client\WebToElasticms\Config\WebResource;
use App\Client\WebToElasticms\Filter\Attr\DataLink;
use App\Client\WebToElasticms\Filter\Attr\Src;
use App\Client\WebToElasticms\Filter\Html\ClassCleaner;
use App\Client\WebToElasticms\Filter\Html\InternalLink;
use App\Client\WebToElasticms\Filter\Html\Striptag;
use App\Client\WebToElasticms\Filter\Html\StyleCleaner;
use App\Client\WebToElasticms\Filter\Html\TagCleaner;
use App\Client\WebToElasticms\Helper\Url;
use App\Client\WebToElasticms\Rapport\Rapport;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Html
{
    public const TYPE = 'html';
    private ConfigManager $config;
    private Document $document;
    private Rapport $rapport;

    public function __construct(ConfigManager $config, Document $document, Rapport $rapport)
    {
        $this->config = $config;
        $this->document = $document;
        $this->rapport = $rapport;
    }

    /**
     * @param array<mixed> $data
     */
    public function extractData(WebResource $resource, HttpResult $result, Analyzer $analyzer, array &$data): void
    {
        $stream = $result->getResponse()->getBody();
        $stream->rewind();
        $crawler = new Crawler($stream->getContents());
        $this->autoDiscoverResources($crawler, $resource);
        foreach ($analyzer->getExtractors() as $extractor) {
            $content = $crawler->filter($extractor->getSelector());
            $attribute = $extractor->getAttribute();
            $basket = [];

            for ($i = 0; $i < $content->count(); ++$i) {
                $item = $content->eq($i);
                if (null !== $attribute) {
                    $attributeValue = $item->attr($attribute);
                    if (null !== $attributeValue) {
                        $basket[] = $this->applyAttrFilters($resource, $attributeValue, $extractor, $this->rapport);
                    }
                } else {
                    $basket[] = $this->applyFilters($resource, $item, $extractor, $this->rapport);
                }
            }

            switch ($extractor->getStrategy()) {
                case \App\Client\WebToElasticms\Config\Extractor::FIRST:
                    if (0 === \count($basket)) {
                        $this->rapport->addExtractError($resource, $extractor, $content->count());
                    } else {
                        if (\count($basket) > 1) {
                            $this->rapport->addExtractError($resource, $extractor, $content->count());
                        }
                        $this->assignExtractedProperty($resource, $extractor, $data, $basket[0]);
                    }
                    break;
                case \App\Client\WebToElasticms\Config\Extractor::ONE:
                    if (1 !== \count($basket)) {
                        $this->rapport->addExtractError($resource, $extractor, $content->count());
                    } else {
                        $this->assignExtractedProperty($resource, $extractor, $data, $basket[0]);
                    }
                    break;
                case \App\Client\WebToElasticms\Config\Extractor::ZERO_ONE:
                    if (\count($basket) > 1) {
                        $this->rapport->addExtractError($resource, $extractor, $content->count());
                    } elseif (1 === \count($basket)) {
                        $this->assignExtractedProperty($resource, $extractor, $data, $basket[0]);
                    }
                    break;
                case \App\Client\WebToElasticms\Config\Extractor::N:
                    if (\count($basket) > 0) {
                        $this->assignExtractedProperty($resource, $extractor, $data, $basket);
                    }
                    break;
                default:
                    throw new \RuntimeException('Not supported strategy');
            }
        }
    }

    /**
     * @param array<mixed> $data
     * @param mixed        $content
     */
    protected function assignExtractedProperty(WebResource $resource, \App\Client\WebToElasticms\Config\Extractor $extractor, array &$data, $content): void
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $property = \str_replace(['%locale%'], [$resource->getLocale()], $extractor->getProperty());
        $propertyAccessor->setValue($data, $property, $content);
    }

    private function applyFilters(WebResource $resource, Crawler $content, \App\Client\WebToElasticms\Config\Extractor $extractor, Rapport $rapport): string
    {
        $asHtml = true;
        foreach ($extractor->getFilters() as $filterType) {
            if (\str_starts_with($filterType, DataLink::TYPE)) {
                $length = \strlen(DataLink::TYPE) < \strlen($filterType) ? \strlen(DataLink::TYPE) + 1 : \strlen(DataLink::TYPE);
                $type = \substr($filterType, $length);
                $filter = new \App\Client\WebToElasticms\Filter\Html\DataLink($this->config, $rapport);
                $filter->process($resource, $content, $type);
                $asHtml = false;
            } else {
                switch ($filterType) {
                    case Striptag::TYPE:
                        $filter = new Striptag();
                        $asHtml = false;
                        break;
                    case InternalLink::TYPE:
                        $filter = new InternalLink($this->config, $rapport, $resource->getUrl());
                        break;
                    case StyleCleaner::TYPE:
                        $filter = new StyleCleaner();
                        break;
                    case ClassCleaner::TYPE:
                        $filter = new ClassCleaner($this->config);
                        break;
                    case TagCleaner::TYPE:
                        $filter = new TagCleaner($this->config);
                        break;
                    default:
                        throw new \RuntimeException(\sprintf('Unexpected %s filter', $filterType));
                }
                $filter->process($resource, $content);
            }
        }

        return $asHtml ? $content->html() : $content->text();
    }

    /**
     * @return mixed
     */
    private function applyAttrFilters(WebResource $resource, string $content, \App\Client\WebToElasticms\Config\Extractor $extractor, Rapport $rapport)
    {
        foreach ($extractor->getFilters() as $filterType) {
            $type = '';
            if (\str_starts_with($filterType, DataLink::TYPE)) {
                $length = \strlen(DataLink::TYPE) < \strlen($filterType) ? \strlen(DataLink::TYPE) + 1 : \strlen(DataLink::TYPE);
                $type = \substr($filterType, $length);
                $filterType = DataLink::TYPE;
            }
            switch ($filterType) {
                case Src::TYPE:
                    if (!\is_string($content)) {
                        throw new \RuntimeException(\sprintf('Unexpected non string content for filter %s', Src::TYPE));
                    }
                    $filter = new Src($this->config, $resource->getUrl(), $this->rapport);
                    $content = $filter->process($content);
                    break;
                case DataLink::TYPE:
                    if (!\is_string($content)) {
                        throw new \RuntimeException(\sprintf('Unexpected non string content for filter %s', DataLink::TYPE));
                    }
                    $filter = new DataLink($this->config, $resource->getUrl(), $rapport);
                    $content = $filter->process($content, $type);
                    break;
                default:
                    throw new \RuntimeException(\sprintf('Unexpected %s filter', $filterType));
            }
        }

        return $content;
    }

    private function autoDiscoverResources(Crawler $crawler, WebResource $resource): void
    {
        $cssSelector = $this->config->getAutoDiscoverResourcesLink();
        if (null === $cssSelector) {
            return;
        }
        foreach ($this->config->getLocales() as $locale) {
            if ($this->document->hasResourceFor($locale)) {
                continue;
            }
            $content = $crawler->filter(\str_replace('%locale%', $locale, $cssSelector));
            if (1 !== $content->count()) {
                continue;
            }
            $path = $content->attr('href');
            if (!\is_string($path)) {
                continue;
            }
            $pattern = $this->config->getIgnoreResourceLinkPattern();
            if (null !== $pattern && \preg_match($pattern, $path)) {
                continue;
            }
            $url = new Url($path, $resource->getUrl());
            $this->document->addResource(new WebResource($url->getUrl(), $locale, $resource->getType()));
        }
    }
}
