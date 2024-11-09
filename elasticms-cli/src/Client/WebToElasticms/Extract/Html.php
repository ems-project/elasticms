<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Extract;

use App\CLI\Client\HttpClient\HttpResult;
use App\CLI\Client\WebToElasticms\Config\Analyzer;
use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Config\Document;
use App\CLI\Client\WebToElasticms\Config\Extractor;
use App\CLI\Client\WebToElasticms\Config\WebResource;
use App\CLI\Client\WebToElasticms\Filter\Attr\DataLink;
use App\CLI\Client\WebToElasticms\Filter\Attr\LinkMediaFile;
use App\CLI\Client\WebToElasticms\Filter\Attr\Src;
use App\CLI\Client\WebToElasticms\Filter\Html\ClassCleaner;
use App\CLI\Client\WebToElasticms\Filter\Html\InternalLink;
use App\CLI\Client\WebToElasticms\Filter\Html\Striptag;
use App\CLI\Client\WebToElasticms\Filter\Html\StyleCleaner;
use App\CLI\Client\WebToElasticms\Filter\Html\TagCleaner;
use App\CLI\Client\WebToElasticms\Rapport\Rapport;
use EMS\CommonBundle\Exception\NotParsableUrlException;
use EMS\CommonBundle\Helper\Url;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Html
{
    final public const TYPE = 'html';

    public function __construct(private readonly ConfigManager $config, private readonly Document $document, private readonly Rapport $rapport)
    {
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
                case Extractor::FIRST:
                    if (0 === \count($basket)) {
                        $this->rapport->addExtractError($resource, $extractor, $content->count());
                    } else {
                        if (\count($basket) > 1) {
                            $this->rapport->addExtractError($resource, $extractor, $content->count());
                        }
                        $this->assignExtractedProperty($resource, $extractor, $data, $basket[0]);
                    }
                    break;
                case Extractor::ONE:
                    if (1 !== \count($basket)) {
                        $this->rapport->addExtractError($resource, $extractor, $content->count());
                    } else {
                        $this->assignExtractedProperty($resource, $extractor, $data, $basket[0]);
                    }
                    break;
                case Extractor::ZERO_ONE:
                    if (\count($basket) > 1) {
                        $this->rapport->addExtractError($resource, $extractor, $content->count());
                    } elseif (1 === \count($basket)) {
                        $this->assignExtractedProperty($resource, $extractor, $data, $basket[0]);
                    }
                    break;
                case Extractor::N:
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
     */
    protected function assignExtractedProperty(WebResource $resource, Extractor $extractor, array &$data, mixed $content): void
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $property = \str_replace(['%locale%'], [$resource->getLocale()], (string) $extractor->getProperty());
        $propertyAccessor->setValue($data, $property, $content);
    }

    private function applyFilters(WebResource $resource, Crawler $content, Extractor $extractor, Rapport $rapport): string
    {
        $asHtml = true;
        foreach ($extractor->getFilters() as $filterType) {
            if (\str_starts_with($filterType, DataLink::TYPE)) {
                $length = \strlen(DataLink::TYPE) < \strlen($filterType) ? \strlen(DataLink::TYPE) + 1 : \strlen(DataLink::TYPE);
                $type = \substr($filterType, $length);
                $filter = new \App\CLI\Client\WebToElasticms\Filter\Html\DataLink($this->config, $rapport);
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
                        $filter = new StyleCleaner($this->config);
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
    private function applyAttrFilters(WebResource $resource, string $content, Extractor $extractor, Rapport $rapport)
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
                case LinkMediaFile::TYPE:
                    if (!\is_string($content)) {
                        throw new \RuntimeException(\sprintf('Unexpected non string content for filter %s', LinkMediaFile::TYPE));
                    }
                    try {
                        $url = new Url($content, $resource->getUrl());
                        $filter = new LinkMediaFile($this->config, $rapport);
                        $content = $filter->process($url, $extractor->getAttribute() ?? 'href');
                    } catch (NotParsableUrlException) {
                        $this->rapport->inAssetsError($content, $resource->getUrl(), 'NotParsableUrlException');
                    }
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
