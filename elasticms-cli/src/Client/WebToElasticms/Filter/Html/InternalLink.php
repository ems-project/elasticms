<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Filter\Html;

use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Config\WebResource;
use App\CLI\Client\WebToElasticms\Rapport\Rapport;
use EMS\CommonBundle\Exception\NotParsableUrlException;
use EMS\CommonBundle\Helper\Url;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler;

class InternalLink implements HtmlInterface
{
    final public const string TYPE = 'internal-link';

    public function __construct(private readonly ConfigManager $config, private readonly Rapport $rapport, private readonly string $currentUrl)
    {
    }

    #[\Override]
    public function process(WebResource $resource, Crawler $content): void
    {
        $this->convertAttribute($resource, $content, 'src');
        $this->convertAttribute($resource, $content, 'href');
    }

    protected function convertAttribute(WebResource $resource, Crawler $content, string $attribute): void
    {
        foreach ($content->filter("[$attribute]") as $item) {
            if (!$item instanceof \DOMElement) {
                throw new \RuntimeException('Unexpected non DOMElement object');
            }

            $href = $item->getAttribute($attribute);
            if (\str_starts_with($href, 'ems://')) {
                continue;
            }
            try {
                $url = new Url($href, $this->currentUrl);
            } catch (NotParsableUrlException) {
                $this->rapport->inAssetsError($href, $this->currentUrl, 'NotParsableUrlException');
                continue;
            }

            if (\in_array($url->getScheme(), ['mailto'])) {
                continue;
            }
            if (!\in_array($url->getHost(), $this->config->getHosts())) {
                continue;
            }
            $path = $url->getPath();
            if ($this->isLinkToRemove($item, $path)) {
                continue;
            }

            $path = $this->config->mediaFileLink($url, $this->rapport, $attribute);
            if (null !== $path) {
                $item->setAttribute($attribute, $path);
                continue;
            }

            try {
                $path = $this->config->findInternalLink($url, $this->rapport);
                $item->setAttribute($attribute, $path);
            } catch (ClientException|RequestException $e) {
                $this->rapport->addResourceInError($resource, $url, $e->getCode(), $e->getMessage(), 'internal-link');
            }
        }
    }

    private function isLinkToRemove(\DOMNode $item, string $path): bool
    {
        foreach ($this->config->getLinkToClean() as $regex) {
            if (\preg_match($regex, $path)) {
                $parent = $item->parentNode;
                if (!$parent instanceof \DOMElement) {
                    throw new \RuntimeException('Unexpected non DOMElement object');
                }
                $document = $item->ownerDocument;
                if (!$document instanceof \DOMDocument || null === $item->nodeValue) {
                    throw new \RuntimeException('Unexpected non DOMDocument object');
                }
                $textNode = $document->createTextNode($item->nodeValue);
                $parent->replaceChild($textNode, $item);

                return true;
            }
        }

        return false;
    }
}
