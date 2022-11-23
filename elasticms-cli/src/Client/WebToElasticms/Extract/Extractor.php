<?php

declare(strict_types=1);

namespace App\Client\WebToElasticms\Extract;

use App\Client\HttpClient\CacheManager;
use App\Client\WebToElasticms\Config\Computer;
use App\Client\WebToElasticms\Config\ConfigManager;
use App\Client\WebToElasticms\Config\Document as WebDocument;
use App\Client\WebToElasticms\Config\WebResource;
use App\Client\WebToElasticms\Helper\ExpressionData;
use App\Client\WebToElasticms\Helper\Url;
use App\Client\WebToElasticms\Rapport\Rapport;
use EMS\CommonBundle\Common\Standard\Json;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Extractor
{
    private ConfigManager $config;
    private CacheManager $cache;
    private ExpressionLanguage $expressionLanguage;
    private LoggerInterface $logger;
    private Rapport $rapport;

    public function __construct(ConfigManager $config, CacheManager $cache, LoggerInterface $logger, Rapport $rapport)
    {
        $this->config = $config;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->rapport = $rapport;
        $this->expressionLanguage = $config->getExpressionLanguage();
    }

    public function extractDataCount(): int
    {
        return \count($this->config->getDocuments());
    }

    public function currentStep(): int
    {
        $lastUpdated = $this->config->getLastUpdated();
        if (null === $lastUpdated) {
            return 0;
        }
        $count = 1;
        foreach ($this->config->getDocuments() as $document) {
            if ($document->getOuuid() === $lastUpdated) {
                return $count;
            }
            ++$count;
        }

        return 0;
    }

    /**
     * @return iterable<ExtractedData>
     */
    public function extractData(Rapport $rapport, ?string $ouuid): iterable
    {
        $lastUpdated = $this->config->getLastUpdated();
        $found = (null === $lastUpdated);
        foreach ($this->config->getDocuments() as $document) {
            if (!$found) {
                $found = ($document->getOuuid() === $lastUpdated);
                continue;
            }
            if (null !== $ouuid && $ouuid !== $document->getOuuid()) {
                continue;
            }
            $defaultData = $document->getDefaultData();
            $data = $defaultData;
            $withoutError = true;
            $emptyExtractor = false;
            foreach ($document->getResources() as $resource) {
                $this->logger->notice(\sprintf('Start extracting from %s', $resource->getUrl()));
                if (EmptyExtractor::TYPE === $this->config->getAnalyzer($resource->getType())->getType()) {
                    $emptyExtractor = true;
                } else {
                    try {
                        $this->extractDataFromResource($document, $resource, $data);
                    } catch (ClientException|RequestException $e) {
                        $rapport->addResourceInError($resource, new Url($resource->getUrl()), $e->getCode(), $e->getMessage());
                        $withoutError = false;
                    }
                }
            }

            if ($withoutError && $data === $defaultData && !$emptyExtractor) {
                $rapport->addNothingExtracted($document);
                continue;
            }

            if ($emptyExtractor) {
                $hash = $this->hashFromResources($document);
            } else {
                $hash = \sha1(Json::encode($data));
            }

            $type = $this->config->getType($document->getType());
            foreach ($type->getComputers() as $computer) {
                if (!$this->condition($computer, $data, $document)) {
                    continue;
                }
                $value = $this->compute($computer, $data, $document);
                $this->assignComputedProperty($computer, $data, $value);
            }

            foreach ($type->getTempFields() as $tempFields) {
                if (isset($data[$tempFields])) {
                    unset($data[$tempFields]);
                }
            }

            $data[$this->config->getHashResourcesField()] = $hash;

            yield new ExtractedData($document, $data);
        }
    }

    /**
     * @param array<mixed> $data
     */
    private function extractDataFromResource(WebDocument $document, WebResource $resource, array &$data): void
    {
        $result = $this->cache->get($resource->getUrl());
        $analyzer = $this->config->getAnalyzer($resource->getType());
        switch ($analyzer->getType()) {
            case Html::TYPE:
                $extractor = new Html($this->config, $document, $this->rapport);
                break;
            default:
                throw new \RuntimeException(\sprintf('Type of analyzer %s unknown', $analyzer->getType()));
        }
        $extractor->extractData($resource, $result, $analyzer, $data);
    }

    /**
     * @param array<mixed> $data
     */
    private function condition(Computer $computer, array &$data, WebDocument $document): bool
    {
        $condition = $this->expressionLanguage->evaluate($computer->getCondition(), $context = [
            'data' => new ExpressionData($data),
            'document' => $document,
        ]);
        if (!\is_bool($condition)) {
            throw new \RuntimeException(\sprintf('Condition "%s" must return a boolean', $computer->getCondition()));
        }

        return $condition;
    }

    /**
     * @param array<mixed> $data
     *
     * @return mixed
     */
    private function compute(Computer $computer, array &$data, WebDocument $document)
    {
        $value = $this->expressionLanguage->evaluate($computer->getExpression(), $context = [
            'data' => new ExpressionData($data),
            'document' => $document,
        ]);

        if ($computer->isJsonDecode() && \is_string($value)) {
            if (\in_array(\trim($value), ['null', ''])) {
                return null;
            }

            return Json::decode($value);
        }

        return $value;
    }

    /**
     * @param array<mixed>             $data
     * @param string|array<mixed>|null $value
     */
    private function assignComputedProperty(Computer $computer, array &$data, $value): void
    {
        $property = Document::fieldPathToPropertyPath($computer->getProperty());
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyAccessor->setValue($data, $property, $value);
    }

    public function reset(): void
    {
        $this->config->setLastUpdated(null);
    }

    private function hashFromResources(WebDocument $document): string
    {
        $hashContext = \hash_init('sha1');
        foreach ($document->getResources() as $resource) {
            $handler = $this->cache->get($resource->getUrl())->getStream();
            if (0 !== $handler->tell()) {
                $handler->rewind();
            }
            while (!$handler->eof()) {
                \hash_update($hashContext, $handler->read(1024 * 1024));
            }
        }

        return \hash_final($hashContext);
    }
}
