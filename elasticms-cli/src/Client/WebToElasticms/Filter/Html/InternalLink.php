<?php

declare(strict_types=1);

namespace App\Client\WebToElasticms\Filter\Html;

use App\Client\WebToElasticms\Config\ConfigManager;
use App\Client\WebToElasticms\Config\WebResource;
use App\Client\WebToElasticms\Helper\Url;
use App\Client\WebToElasticms\Rapport\Rapport;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class InternalLink implements HtmlInterface
{
    public const TYPE = 'internal-link';
    private ConfigManager $config;
    private string $currentUrl;
    private LoggerInterface $logger;
    private Rapport $rapport;

    public function __construct(LoggerInterface $logger, ConfigManager $config, Rapport $rapport, string $currentUrl)
    {
        $this->config = $config;
        $this->currentUrl = $currentUrl;
        $this->logger = $logger;
        $this->rapport = $rapport;
    }

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
            if (0 === \strpos($href, 'ems://')) {
                continue;
            }
            $url = new Url($href, $this->currentUrl);

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
            try {
                $path = $this->config->findInternalLink($url, $this->rapport);
                $item->setAttribute($attribute, $path);
            } catch (ClientException $e) {
                $this->rapport->addResourceInError($resource, $url, $e->getCode(), $e->getMessage(), 'internal-link');
            } catch (RequestException $e) {
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
                if (!$document instanceof \DOMDocument) {
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
